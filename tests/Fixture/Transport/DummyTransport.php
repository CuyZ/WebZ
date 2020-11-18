<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Tests\Fixture\Transport;

use CuyZ\WebZ\Core\Result\RawResult;
use CuyZ\WebZ\Core\Transport\Transport;

final class DummyTransport implements Transport
{
    private array $raw;

    public function __construct(array $raw = [])
    {
        $this->raw = $raw;
    }

    public function send(object $payload): RawResult
    {
        return RawResult::ok($this->raw);
    }
}
