<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Tests\EndToEnd\Bus;

use CuyZ\WebZ\Core\Bus\WebServiceBus;
use CuyZ\WebZ\Tests\Fixture\Transport\DummyExceptionTransport;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyCustomExceptionsWebService;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyWebService;
use Exception;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @coversNothing
 */
final class ExceptionHandlingTest extends TestCase
{
    public function test_throws_exception_as_is()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('foo');

        $bus = WebServiceBus::builder()
            ->withTransport(new DummyExceptionTransport('foo'))
            ->build();

        $bus->call(new DummyWebService(new stdClass()));
    }

    public function test_throws_custom_exception()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('bar');

        $bus = WebServiceBus::builder()
            ->withTransport(new DummyExceptionTransport('foo'))
            ->build();

        $bus->call(new DummyCustomExceptionsWebService('bar'));
    }
}
