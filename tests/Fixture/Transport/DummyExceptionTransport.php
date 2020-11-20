<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Tests\Fixture\Transport;

use CuyZ\WebZ\Core\Result\RawResult;
use CuyZ\WebZ\Core\Transport\AsyncTransport;
use CuyZ\WebZ\Core\Transport\Transport;
use Exception;
use GuzzleHttp\Promise\PromiseInterface;

final class DummyExceptionTransport implements Transport, AsyncTransport
{
    private string $message;

    public function __construct(string $message = 'foo')
    {
        $this->message = $message;
    }

    public function send(object $payload): RawResult
    {
        throw new Exception($this->message);
    }

    public function sendAsync(object $payload, ?string $payloadGroupHash): ?PromiseInterface
    {
        throw new Exception($this->message);
    }
}
