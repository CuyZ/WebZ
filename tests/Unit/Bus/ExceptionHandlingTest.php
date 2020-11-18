<?php

use CuyZ\WebZ\Core\Bus\Bus;
use CuyZ\WebZ\Tests\Fixture\Transport\DummyExceptionTransport;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyExceptionsWebService;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyWebService;

it('throws exception as-is', function () {
    $bus = Bus::builder()
        ->withTransport(new DummyExceptionTransport('foo'))
        ->build();

    $bus->call(new DummyWebService(new stdClass()));
})->throws(Exception::class, 'foo');

it('throws custom exception', function () {
    $bus = Bus::builder()
        ->withTransport(new DummyExceptionTransport('foo'))
        ->build();

    $bus->call(new DummyExceptionsWebService('bar'));
})->throws(Exception::class, 'bar');
