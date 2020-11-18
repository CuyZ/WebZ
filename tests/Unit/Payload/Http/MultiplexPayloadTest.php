<?php

use CuyZ\WebZ\Http\Payload\MultiplexPayload;
use CuyZ\WebZ\Http\Transformer\AutoTransformer;

it('creates an instance', function (?float $timeout) {
    $payload = new MultiplexPayload($timeout);

    expect($payload->streamTimeout())->toBe($timeout);
})->with([
    null,
    12.34
]);

it('adds a request', function () {
    $payload = new MultiplexPayload();

    expect($payload->requests())->toBeEmpty();

    $payload->with('a', 'http://example.com/a', ['foo' => 'bar'], new AutoTransformer());
    $payload->with('b', 'http://example.com/b');

    expect($payload->requests())->toHaveCount(2);

    $requests = $payload->requests();

    expect($requests[0]->method())->toBe('a');
    expect($requests[0]->url())->toBe('http://example.com/a');
    expect($requests[0]->options())->toBe(['foo' => 'bar']);
    expect($requests[0]->transformer())->toBeInstanceOf(AutoTransformer::class);
});
