<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Tests\Contract\Transport;

use CuyZ\WebZ\Core\Transport\AsyncTransport;
use CuyZ\WebZ\Core\Transport\Transport;
use PHPUnit\Framework\TestCase;
use stdClass;

abstract class TransportContractTest extends TestCase
{
    abstract protected function transport(): Transport;

    public function test_returns_null_for_an_incompatible_payload(): void
    {
        $transport = $this->transport();

        $result = $transport->send(new stdClass());

        self::assertNull($result);

        if ($transport instanceof AsyncTransport) {
            $asyncResult = $transport->sendAsync(new stdClass(), null);

            self::assertNull($asyncResult);
        }
    }
}
