<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Tests\Fixture\Transport;

use CuyZ\WebZ\Core\Result\RawResult;
use CuyZ\WebZ\Core\Transport\AsyncTransport;
use CuyZ\WebZ\Core\Transport\Transport;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;

final class DummyDynamicExceptionTransport implements Transport, AsyncTransport
{
    public function sendAsync(object $payload, ?string $asyncCallHash): ?PromiseInterface
    {
        if ($payload instanceof \Exception) {
            throw $payload;
        }

        return new FulfilledPromise(RawResult::ok([]));
    }

    public function send(object $payload): ?RawResult
    {
        return $this->sendAsync($payload, null)->wait();
    }
}
