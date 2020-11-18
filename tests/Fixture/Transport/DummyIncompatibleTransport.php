<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Tests\Fixture\Transport;

use CuyZ\WebZ\Core\Result\RawResult;
use CuyZ\WebZ\Core\Transport\Transport;

final class DummyIncompatibleTransport implements Transport
{
    public function send(object $payload): ?RawResult
    {
        return null;
    }
}
