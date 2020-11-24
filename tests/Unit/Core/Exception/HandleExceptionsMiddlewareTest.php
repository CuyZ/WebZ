<?php

namespace CuyZ\WebZ\Tests\Unit\Core\Exception;

use CuyZ\WebZ\Core\Bus\Pipeline\Next;
use CuyZ\WebZ\Core\Exception\HandleExceptionsMiddleware;
use CuyZ\WebZ\Core\Result\Result;
use CuyZ\WebZ\Tests\Fixture\Mocks;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyCustomExceptionsWebService;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyWebService;
use Exception;
use GuzzleHttp\Promise\FulfilledPromise;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \CuyZ\WebZ\Core\Exception\HandleExceptionsMiddleware
 */
class HandleExceptionsMiddlewareTest extends TestCase
{
    public function nextDataProvider()
    {
        return [
            [
                new Next(function () {
                    throw new Exception('foo');
                }),
            ],
            [
                new Next(function () {
                    return new FulfilledPromise(Mocks::resultErr(new Exception('foo')));
                }),
            ],
        ];
    }

    /**
     * @dataProvider nextDataProvider
     * @param Next $next
     */
    public function test_with_custom_exception_handling(Next $next)
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('bar');

        $webservice = new DummyCustomExceptionsWebService('bar');
        $middleware = new HandleExceptionsMiddleware();

        $middleware->process($webservice, $next)->wait();
    }

    public function test_without_custom_exception_handling()
    {
        $exception = new Exception('foo', 1604443644);

        $this->expectExceptionObject($exception);

        $webservice = new DummyWebService(new stdClass());
        $middleware = new HandleExceptionsMiddleware();

        $next = new Next(function () use ($exception) {
            throw $exception;
        });

        $middleware->process($webservice, $next)->wait();
    }

    public function test_returns_the_result_if_no_exception_is_thrown()
    {
        $webservice = new DummyCustomExceptionsWebService('bar');
        $middleware = new HandleExceptionsMiddleware();

        $next = new Next(fn() => Mocks::promiseOk());

        /** @var Result $result */
        $result = $middleware->process($webservice, $next)->wait();

        self::assertInstanceOf(Result::class, $result);
    }
}
