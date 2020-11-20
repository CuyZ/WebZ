<?php

use CuyZ\WebZ\Core\Bus\Pipeline\Next;
use CuyZ\WebZ\Core\Exception\HandleExceptionsMiddleware;
use CuyZ\WebZ\Core\Result\Result;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyExceptionsWebService;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyWebService;
use GuzzleHttp\Promise\FulfilledPromise;
use Tests\Mocks;

dataset('nexts', [
    new Next(function () {
        throw new Exception('foo');
    }),
    new Next(function () {
        return new FulfilledPromise(Mocks::resultErr(new Exception('foo')));
    }),
]);

test('with custom exception handling', function (Next $next) {
    $webservice = new DummyExceptionsWebService('bar');
    $middleware = new HandleExceptionsMiddleware();

    $middleware->process($webservice, $next)->wait();
})
    ->with('nexts')
    ->throws(Exception::class, 'bar');

$exception = new Exception('foo', 1604443644);

test('without custom exception handling', function () use ($exception) {
    $webservice = new DummyWebService(new stdClass());
    $middleware = new HandleExceptionsMiddleware();

    $next = new Next(function () use ($exception) {
        throw $exception;
    });

    $middleware->process($webservice, $next)->wait();;
})->expectExceptionObject($exception);

it('returns the result if no exception is thrown', function () {
    $webservice = new DummyExceptionsWebService('bar');
    $middleware = new HandleExceptionsMiddleware();

    $next = new Next(fn() => Mocks::promiseOk());

    /** @var Result $result */
    $result = $middleware->process($webservice, $next)->wait();

    expect($result)->toBeInstanceOf(Result::class);
});
