<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Core\Transport;

use CuyZ\WebZ\Core\Bus\Middleware;
use CuyZ\WebZ\Core\Bus\Pipeline\Next;
use CuyZ\WebZ\Core\Exception\NoCompatibleTransportException;
use CuyZ\WebZ\Core\Result\RawResult;
use CuyZ\WebZ\Core\Result\Result;
use CuyZ\WebZ\Core\Support\Timer;
use CuyZ\WebZ\Core\WebService;
use Exception;

final class CallTransportMiddleware implements Middleware
{
    /** @var Transport[] */
    private array $transports;

    /**
     * @param Transport[] $transports
     */
    public function __construct(array $transports)
    {
        $this->transports = $transports;
    }

    public function process(WebService $webService, Next $next): Result
    {
        $raw = null;

        $timer = Timer::start();

        foreach ($this->transports as $transport) {
            $timer = Timer::start();

            try {
                $raw = $transport->send($webService->getPayload());
            } catch (Exception $e) {
                $raw = RawResult::err($e);
            }

            if ($raw instanceof RawResult) {
                break;
            }
        }

        if (!$raw instanceof RawResult) {
            throw new NoCompatibleTransportException($webService->getPayload());
        }

        $timer->stop();

        return new Result($raw, $timer);
    }
}
