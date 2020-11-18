<?php

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
