<?php

use CuyZ\WebZ\Core\Bus\Pipeline\Next;
use CuyZ\WebZ\Core\Exception\NoCompatibleTransportException;
use CuyZ\WebZ\Core\Result\Result;
use CuyZ\WebZ\Core\Transport\CallTransportMiddleware;
use CuyZ\WebZ\Tests\Fixture\Transport\DummyExceptionTransport;
use CuyZ\WebZ\Tests\Fixture\Transport\DummyIncompatibleTransport;
use CuyZ\WebZ\Tests\Fixture\Transport\DummyTransport;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyWebService;

it('throws when no transport is found', function () {
    $webservice = new DummyWebService(new stdClass());
    $middleware = new CallTransportMiddleware([]);

    $next = new Next(fn () => null);

    $middleware->process($webservice, $next);
})->throws(NoCompatibleTransportException::class);

it('calls the correct transport', function () {
    $webservice = new DummyWebService(new stdClass());

    $middleware = new CallTransportMiddleware([
        new DummyIncompatibleTransport(),
        new DummyTransport(['foo' => 'bar']),
    ]);

    $next = new Next(fn () => null);

    $result = $middleware->process($webservice, $next);

    expect($result)->toBeInstanceOf(Result::class);
    expect($result->exception())->toBeNull();
    expect($result->data())->toBe(['foo' => 'bar']);
});

it('returns an error result on transport error', function () {
    $webservice = new DummyWebService(new stdClass());

    $middleware = new CallTransportMiddleware([
        new DummyExceptionTransport('abc'),
    ]);

    $next = new Next(fn () => null);

    $result = $middleware->process($webservice, $next);

    expect($result)->toBeInstanceOf(Result::class);
    expect($result->exception())->toBeInstanceOf(Exception::class);
    expect($result->exception()->getMessage())->toBe('abc');
});
