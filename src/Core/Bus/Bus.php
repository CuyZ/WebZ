<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Core\Bus;

use CuyZ\WebZ\Core\Bus\Pipeline\Pipeline;
use CuyZ\WebZ\Core\Result\Result;
use CuyZ\WebZ\Core\WebService;
use GuzzleHttp\Promise\PromiseInterface;

final class Bus
{
    private Pipeline $pipeline;

    public function __construct(Pipeline $pipeline)
    {
        $this->pipeline = $pipeline;
    }

    public static function builder(): BusBuilder
    {
        return new BusBuilder();
    }

    /**
     * @param WebService $webService
     * @return mixed
     */
    public function call(WebService $webService)
    {
        return $this->dispatch($webService)->wait();
    }

    /**
     * @param WebService ...$webServices
     * @return PromiseInterface[]
     */
    public function callAsync(WebService ...$webServices)
    {
        if (count($webServices) === 0) {
            throw new NoWebServiceException();
        }

        $payloads = array_map(fn(WebService $webService): string => $webService->getPayloadHash(), $webServices);
        $payloads = implode('', $payloads);

        $payloadGroupId = sha1(serialize($payloads));

        return array_map(
            function (WebService $webService) use ($payloadGroupId): PromiseInterface {
                $webService->markAsAsyncCall($payloadGroupId);

                return $this->dispatch($webService);
            },
            $webServices
        );
    }

    private function dispatch(WebService $webService): PromiseInterface
    {
        return $this->pipeline
            ->dispatch($webService)
            ->then(fn(Result $result) => $webService->parse($result->data()));
    }
}
