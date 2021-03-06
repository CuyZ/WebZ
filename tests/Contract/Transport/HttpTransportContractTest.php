<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Tests\Contract\Transport;

use CuyZ\WebZ\Core\Transport\Transport;
use CuyZ\WebZ\Http\HttpTransport;
use stdClass;

/**
 * @covers \CuyZ\WebZ\Http\HttpTransport
 */
final class HttpTransportContractTest extends TransportContractTest
{
    protected function transport(): Transport
    {
        return new HttpTransport();
    }

    protected function incompatiblePayload(): object
    {
        return new stdClass();
    }
}
