<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Tests\Fixture\Transport;

use CuyZ\WebZ\Core\Result\RawResult;
use CuyZ\WebZ\Core\Transport\AsyncTransport;
use CuyZ\WebZ\Core\Transport\Transport;
use CuyZ\WebZ\Tests\Fixture\Utils;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;

final class DummyTransport implements Transport, AsyncTransport
{
    private array $raw;

    public function __construct(array $raw = [])
    {
        $this->raw = $raw;
    }

    public function send(object $payload): RawResult
    {
        return $this->sendAsync($payload, Utils::random(self::class))->wait();
    }

    public function sendAsync(object $payload, string $payloadGroupHash): ?PromiseInterface
    {
        return new FulfilledPromise(RawResult::ok($this->raw));
    }
}
