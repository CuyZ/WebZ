<?php

namespace CuyZ\WebZ\Tests\Unit\Core\Bus;

use CuyZ\WebZ\Core\Bus\BusBuilder;
use CuyZ\WebZ\Core\Transport\NoTransportException;
use PHPUnit\Framework\TestCase;

class BusBuilderTest extends TestCase
{
    public function test_throws_when_no_transport_is_configured()
    {
        $this->expectException(NoTransportException::class);

        (new BusBuilder())->build();
    }
}
