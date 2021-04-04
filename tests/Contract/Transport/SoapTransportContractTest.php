<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Tests\Contract\Transport;

use CuyZ\WebZ\Core\Transport\Transport;
use CuyZ\WebZ\Soap\SoapTransport;
use stdClass;

/**
 * @covers \CuyZ\WebZ\Soap\SoapTransport
 */
final class SoapTransportContractTest extends TransportContractTest
{
    protected function transport(): Transport
    {
        return new SoapTransport();
    }

    protected function incompatiblePayload(): object
    {
        return new stdClass();
    }
}
