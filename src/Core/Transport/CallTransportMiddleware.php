<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Core\Transport;

use CuyZ\WebZ\Core\Bus\Middleware;
use CuyZ\WebZ\Core\Bus\Pipeline\Next;
use CuyZ\WebZ\Core\Exception\NoCompatibleAsyncTransportException;
use CuyZ\WebZ\Core\Exception\NoCompatibleTransportException;
use CuyZ\WebZ\Core\Result\RawResult;
use CuyZ\WebZ\Core\Result\Result;
use CuyZ\WebZ\Core\Support\Timer;
use CuyZ\WebZ\Core\WebService;
use Exception;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;

final class CallTransportMiddleware implements Middleware
{
    /** @var Transport[]|AsyncTransport[] */
    private array $transports;

    /**
     * @param Transport[]|AsyncTransport[] $transports
     */
    public function __construct(array $transports)
    {
        if (count($transports) === 0) {
            throw new NoTransportException();
        }

        $this->transports = $transports;
    }

    public function process(WebService $webService, Next $next): PromiseInterface
    {
        $timer = Timer::start();
        $promise = null;

        foreach ($this->transports as $transport) {
            $timer = Timer::start();

            try {
                $promise = $this->tryTransport($webService, $transport);
            } catch (Exception $e) {
                $promise = new FulfilledPromise(RawResult::err($e));
            }

            if ($promise instanceof PromiseInterface) {
                break;
            }
        }

        if (!$promise instanceof PromiseInterface) {
            if ($webService->isAsyncCall()) {
                throw new NoCompatibleAsyncTransportException($webService->getPayload());
            }

            throw new NoCompatibleTransportException($webService->getPayload());
        }

        return $promise->then(function (RawResult $raw) use ($timer): Result {
            $timer->stop();

            return new Result($raw, $timer);
        });
    }

    private function tryTransport(WebService $webService, Transport $transport): ?PromiseInterface
    {
        if ($webService->isAsyncCall()) {
            if (!$transport instanceof AsyncTransport) {
                return null;
            }

            /** @var string $hash */
            $hash = $webService->getAsyncCallHash();

            return $transport->sendAsync($webService->getPayload(), $hash);
        }

        $raw = $transport->send($webService->getPayload());

        if (null === $raw) {
            return null;
        }

        return new FulfilledPromise($raw);
    }
}
