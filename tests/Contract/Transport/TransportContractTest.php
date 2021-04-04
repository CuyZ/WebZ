<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Tests\Contract\Transport;

use CuyZ\WebZ\Core\Transport\AsyncTransport;
use CuyZ\WebZ\Core\Transport\Transport;
use PHPUnit\Framework\TestCase;

abstract class TransportContractTest extends TestCase
{
    abstract protected function transport(): Transport;

    abstract protected function incompatiblePayload(): object;

    public function test_returns_null_for_an_incompatible_payload(): void
    {
        $transport = $this->transport();

        $result = $transport->send($this->incompatiblePayload());

        self::assertNull($result);

        if ($transport instanceof AsyncTransport) {
            $asyncResult = $transport->sendAsync($this->incompatiblePayload(), null);

            self::assertNull($asyncResult);
        }
    }
}
