<?php

namespace CuyZ\WebZ\Tests\Unit\Core\Transport;

use CuyZ\WebZ\Core\Bus\Pipeline\Next;
use CuyZ\WebZ\Core\Exception\NoCompatibleAsyncTransportException;
use CuyZ\WebZ\Core\Exception\NoCompatibleTransportException;
use CuyZ\WebZ\Core\Result\Result;
use CuyZ\WebZ\Core\Transport\CallTransportMiddleware;
use CuyZ\WebZ\Core\Transport\NoTransportException;
use CuyZ\WebZ\Tests\Fixture\Transport\DummyExceptionTransport;
use CuyZ\WebZ\Tests\Fixture\Transport\DummyIncompatibleTransport;
use CuyZ\WebZ\Tests\Fixture\Transport\DummySynchronousIncompatibleTransport;
use CuyZ\WebZ\Tests\Fixture\Transport\DummyTransport;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyWebService;
use Exception;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \CuyZ\WebZ\Core\Transport\CallTransportMiddleware
 */
class CallTransportMiddlewareTest extends TestCase
{
    public function test_throws_when_no_transport_is_present()
    {
        $this->expectException(NoTransportException::class);

        new CallTransportMiddleware([]);
    }

    public function test_throws_when_no_synchronous_transport_is_found()
    {
        $this->expectException(NoCompatibleTransportException::class);

        $webservice = new DummyWebService(new stdClass());
        $middleware = new CallTransportMiddleware([
            new DummyIncompatibleTransport(),
        ]);

        $next = new Next(fn() => null);

        $middleware->process($webservice, $next);
    }

    public function test_throws_when_no_asynchronous_transport_is_found()
    {
        $this->expectException(NoCompatibleAsyncTransportException::class);

        $webservice = new DummyWebService(new stdClass());
        $webservice->setPayloadGroupHash('foo');

        $middleware = new CallTransportMiddleware([
            new DummySynchronousIncompatibleTransport(),
        ]);

        $next = new Next(fn() => null);

        $middleware->process($webservice, $next);
    }

    public function test_calls_the_correct_synchronous_transport()
    {
        $webservice = new DummyWebService(new stdClass());

        $middleware = new CallTransportMiddleware([
            new DummyIncompatibleTransport(),
            new DummyTransport(['foo' => 'bar']),
        ]);

        $next = new Next(fn() => null);

        /** @var Result $result */
        $result = $middleware->process($webservice, $next)->wait();

        self::assertInstanceOf(Result::class, $result);
        self::assertNull($result->exception());
        self::assertSame(['foo' => 'bar'], $result->data());
    }

    public function test_calls_the_correct_asynchronous_transport()
    {
        $webservice = new DummyWebService(new stdClass());
        $webservice->setPayloadGroupHash('foo');

        $middleware = new CallTransportMiddleware([
            new DummyIncompatibleTransport(),
            new DummyTransport(['foo' => 'bar']),
        ]);

        $next = new Next(fn() => null);

        /** @var Result $result */
        $result = $middleware->process($webservice, $next)->wait();

        self::assertInstanceOf(Result::class, $result);
        self::assertNull($result->exception());
        self::assertSame(['foo' => 'bar'], $result->data());
    }

    public function test_returns_an_error_result_on_transport_exception()
    {
        $webservice = new DummyWebService(new stdClass());

        $middleware = new CallTransportMiddleware([
            new DummyExceptionTransport('abc'),
        ]);

        $next = new Next(fn() => null);

        /** @var Result $result */
        $result = $middleware->process($webservice, $next)->wait();

        self::assertInstanceOf(Result::class, $result);
        self::assertInstanceOf(Exception::class, $result->exception());
        self::assertSame('abc', $result->exception()->getMessage());
    }
}
