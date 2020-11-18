<?php

use CuyZ\WebZ\Http\Payload\HttpPayload;
use CuyZ\WebZ\Http\Payload\MultiplexPayload;
use CuyZ\WebZ\Http\Payload\RequestPayload;
use CuyZ\WebZ\Http\Transformer\JsonTransformer;

it('creates a single request payload', function () {
    $payload = HttpPayload::request('foo', 'bar');

    expect($payload)->toBeInstanceOf(RequestPayload::class);
    expect($payload->method())->toBe('foo');
    expect($payload->url())->toBe('bar');
});

it('creates a multiplex payload without a timeout', function () {
    $payload = HttpPayload::multiplex();

    expect($payload)->toBeInstanceOf(MultiplexPayload::class);
    expect($payload->streamTimeout())->toBeNull();
});

it('creates a multiplex payload with a timeout', function () {
    $payload = HttpPayload::multiplex(12.34);

    expect($payload)->toBeInstanceOf(MultiplexPayload::class);
    expect($payload->streamTimeout())->toBe(12.34);
});

it('holds a Transformer instance', function (HttpPayload $payload) {
    expect($payload->transformer())->toBeNull();

    $transformer = new JsonTransformer();

    $payload->withTransformer($transformer);

    expect($payload->transformer())->toBe($transformer);
})->with([
    HttpPayload::request('foo', 'bar'),
    HttpPayload::multiplex(),
]);
