<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Tests\Fixture\Transport;

use CuyZ\WebZ\Core\Result\RawResult;
use CuyZ\WebZ\Core\Transport\AsyncTransport;
use CuyZ\WebZ\Core\Transport\Transport;
use GuzzleHttp\Promise\PromiseInterface;

final class DummyIncompatibleTransport implements Transport, AsyncTransport
{
    public function send(object $payload): ?RawResult
    {
        return null;
    }

    public function sendAsync(object $payload, ?string $payloadGroupHash): ?PromiseInterface
    {
        return null;
    }
}
