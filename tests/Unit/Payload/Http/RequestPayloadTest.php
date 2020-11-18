<?php

use CuyZ\WebZ\Http\Exception\MissingConfigException;
use CuyZ\WebZ\Http\Payload\RequestPayload;
use CuyZ\WebZ\Http\Transformer\JsonTransformer;

it('creates an instance', function () {
    $payload = new RequestPayload('foo', 'bar');

    expect($payload->method())->toBe('foo');
    expect($payload->url())->toBe('bar');
    expect($payload->options())->toBeEmpty();
});

it('throws on missing method configuration', function () {
    $payload = new RequestPayload();

    $payload->method();
})->throws(MissingConfigException::class, 'The option "method" is missing');

it('throws on missing url configuration', function () {
    $payload = new RequestPayload();

    $payload->url();
})->throws(MissingConfigException::class, 'The option "url" is missing');

it('does not throw on missing url configuration if "base_uri" is set', function () {
    $payload = new RequestPayload();

    $payload->withBaseUri('foo');

    expect($payload->url())->toBe('');
});

it('saves a "method" option', function () {
    $payload = new RequestPayload('foo');

    expect($payload->method())->toBe('foo');

    $payload->withMethod('bar');

    expect($payload->method())->toBe('bar');
});

it('saves a "url" option', function () {
    $payload = new RequestPayload(null, 'foo');

    expect($payload->url())->toBe('foo');

    $payload->withUrl('bar');

    expect($payload->url())->toBe('bar');
});

it('saves a "base_uri" options', function () {
    $payload = new RequestPayload();

    $payload->withBaseUri('foo');

    expect($payload->options())->toBe([
        'base_uri' => 'foo',
    ]);
});

it('saves a "body" option', function () {
    $payload = new RequestPayload('foo', 'bar');

    $payload->withBody('fiz');

    expect($payload->options())->not->toHaveKey('json');
    expect($payload->options())->toBe(['body' => 'fiz']);
});

it('saves a "json body" option', function () {
    $payload = new RequestPayload('foo', 'bar');

    $payload->withJson(['a' => 'b']);

    expect($payload->options())->not->toHaveKey('body');
    expect($payload->options())->toBe(['json' => ['a' => 'b']]);
});

it('sets a Transformer instance', function () {
    $payload = new RequestPayload('foo', 'bar');

    expect($payload->transformer())->toBeNull();

    $transformer = new JsonTransformer();
    $payload->withTransformer($transformer);

    expect($payload->transformer())->toBe($transformer);
});

it('saves "query" options', function () {
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

it('saves "header" options', function () {
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

it('saves "basic auth" options without password', function () {
    $payload = new RequestPayload('foo', 'bar');

    $payload->withAuthBasic('fiz');

    expect($payload->options())->toBe([
        'auth' => ['fiz'],
    ]);
});

it('saves "basic auth" options with password', function () {
    $payload = new RequestPayload('foo', 'bar');

    $payload->withAuthBasic('fiz', 'baz');

    expect($payload->options())->toBe([
        'auth' => ['fiz', 'baz'],
    ]);
});

it('saves "bearer auth" option', function () {
    $payload = new RequestPayload('foo', 'bar');

    $payload->withAuthBearer('foo');

    expect($payload->options())->toBe([
        'headers' => [
            'Authorization' => ['Bearer foo'],
        ],
    ]);
});
