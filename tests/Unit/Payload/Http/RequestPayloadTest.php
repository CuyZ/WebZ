<?php

use CuyZ\WebZ\Http\Payload\RequestPayload;
use CuyZ\WebZ\Http\Transformer\AutoTransformer;

it('creates an instance', function () {
    $payload = new RequestPayload('foo', 'bar');

    expect($payload->method())->toBe('foo');
    expect($payload->url())->toBe('bar');
    expect($payload->options())->toBeEmpty();
});

it('saves options', function () {
    $payload = new RequestPayload('foo', 'bar');
    $payload->withOptions(['foo' => 'bar']);

    expect($payload->options())->toBe(['foo' => 'bar']);
});

it('saves a body option', function () {
    $payload = new RequestPayload('foo', 'bar');

    $payload->withOptions(['body' => 'foo', 'json' => 'bar']);

    $payload->withBody('fiz');

    expect($payload->options())->not->toHaveKey('json');
    expect($payload->options())->toBe(['body' => 'fiz']);
});

it('saves a json body option', function () {
    $payload = new RequestPayload('foo', 'bar');

    $payload->withOptions(['body' => 'foo', 'json' => 'bar']);

    $payload->withJson(['a' => 'b']);

    expect($payload->options())->not->toHaveKey('body');
    expect($payload->options())->toBe(['json' => ['a' => 'b']]);
});

it('sets a Transformer instance', function () {
    $payload = new RequestPayload('foo', 'bar');

    expect($payload->transformer())->toBeNull();

    $transformer = new AutoTransformer();
    $payload->withTransformer($transformer);

    expect($payload->transformer())->toBe($transformer);
});

it('saves query options', function () {
    $payload = new RequestPayload('foo', 'bar');

    $payload->withQuery('foo', 'a');
    $payload->withQuery('bar', 'b');

    expect($payload->options())->toBe([
        'query' => [
            'foo' => 'a',
            'bar' => 'b',
        ],
    ]);
});

it('saves header options', function () {
    $payload = new RequestPayload('foo', 'bar');

    $payload->withHeader('Foo', 'a');

    expect($payload->options())->toBe([
        'headers' => [
            'Foo' => ['a'],
        ],
    ]);

    $payload->withHeader('Foo', 'b');

    expect($payload->options())->toBe([
        'headers' => [
            'Foo' => ['a', 'b'],
        ],
    ]);

    $payload->withHeader('Bar', 'a');

    expect($payload->options())->toBe([
        'headers' => [
            'Foo' => ['a', 'b'],
            'Bar' => ['a'],
        ],
    ]);
});

it('saves basic auth options without password', function () {
    $payload = new RequestPayload('foo', 'bar');

    $payload->withAuthBasic('fiz');

    expect($payload->options())->toBe([
        'auth_basic' => 'fiz',
    ]);
});

it('saves basic auth options with password', function () {
    $payload = new RequestPayload('foo', 'bar');

    $payload->withAuthBasic('fiz', 'baz');

    expect($payload->options())->toBe([
        'auth_basic' => 'fiz:baz',
    ]);
});
