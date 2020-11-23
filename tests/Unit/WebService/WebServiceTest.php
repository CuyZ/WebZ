<?php

use CuyZ\WebZ\Core\Exception\NotAsyncCallException;
use CuyZ\WebZ\Core\Exception\PayloadGroupHashAlreadySetException;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyCustomPayloadHashWebService;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyRandomPayloadWebService;

it('payload is memoized', function () {
    $webService = new DummyRandomPayloadWebService();

    $payload1 = $webService->getPayload();
    $payload2 = $webService->getPayload();

    expect($payload1)->toBe($payload2);
});

it('can have a custom hash', function () {
    $webService = new DummyCustomPayloadHashWebService('foo');

    expect($webService->getPayloadHash())->toBe('foo');
});

it('sets payload group hash', function () {
    $webService = new DummyRandomPayloadWebService();

    $webService->setPayloadGroupHash('foo');

    expect($webService->getPayloadGroupHash())->toBe('foo');
});

it('throws on unset payload group hash', function () {
    $webService = new DummyRandomPayloadWebService();

    $webService->getPayloadGroupHash();
})->throws(NotAsyncCallException::class);

it('knows if it is an async call', function () {
    $webService = new DummyRandomPayloadWebService();

    expect($webService->isAsyncCall())->toBeFalse();

    $webService->setPayloadGroupHash('foo');

    expect($webService->isAsyncCall())->toBeTrue();
});

it('throws if payload group hash is overridden', function () {
    $webService = new DummyRandomPayloadWebService();

    $webService->setPayloadGroupHash('foo');
    $webService->setPayloadGroupHash('foo');
})->throws(PayloadGroupHashAlreadySetException::class);
