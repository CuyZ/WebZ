<?php
/** @noinspection PhpComposerExtensionStubsInspection */

use CuyZ\WebZ\Soap\Exception\MissingSoapActionException;
use CuyZ\WebZ\Soap\SoapPayload;

it('creates a wsdl mode payload', function () {
    $payload = SoapPayload::forWsdl('test.wsdl', 'foo');

    expect($payload->wsdl())->toBe('test.wsdl');
    expect($payload->action())->toBe('foo');
    expect($payload->options())->toBe(SoapPayload::DEFAULT_OPTIONS);
});

it('creates a non-wsdl mode payload', function () {
    $payload = SoapPayload::forNonWsdl('foo', 'bar', 'fiz');

    expect($payload->wsdl())->toBeNull();
    expect($payload->action())->toBe('fiz');

    $options = $payload->options();

    expect($options)
        ->toHaveKey('location')
        ->and($options['location'])
        ->toBe('foo');

    expect($options)
        ->toHaveKey('uri')
        ->and($options['uri'])
        ->toBe('bar');
});

it('location and uri cannot be overridden from options in non-wsdl mode', function () {
    $payload = SoapPayload::forNonWsdl('foo', 'bar', 'fiz');

    $payload->withOptions([
        'location' => 'abc',
        'uri' => 'def',
    ]);

    expect($payload->options())->toBe([
        'location' => 'foo',
        'uri' => 'bar',
    ]);
});

it('has empty arguments by default', function (SoapPayload $payload) {
    expect($payload->arguments())->toBeEmpty();
})->with([
    SoapPayload::forWsdl('test.wsdl', 'foo'),
    SoapPayload::forNonWsdl('foo', 'bar', 'fiz'),
]);

it('overrides default options', function (SoapPayload $payload) {
    $payload->withOptions([]);

    expect($payload->options())->not->toHaveKeys(array_keys(SoapPayload::DEFAULT_OPTIONS));
})->with([
    SoapPayload::forWsdl('test.xsdl', 'foo'),
    SoapPayload::forNonWsdl('foo', 'bar', 'fiz'),
]);

it('sets the location', function (SoapPayload $payload) {
    $payload->withLocation('abcd');

    $options = $payload->options();

    expect($options)
        ->toHaveKey('location')
        ->and($options['location'])
        ->toBe('abcd');
})->with([
    SoapPayload::forWsdl('test.xsdl', 'foo'),
    SoapPayload::forNonWsdl('foo', 'bar', 'fiz'),
]);

it('sets the call arguments', function (SoapPayload $payload) {
    $payload->withArguments(['foo', 'bar']);

    expect($payload->arguments())->toBe(['foo', 'bar']);
})->with([
    SoapPayload::forWsdl('test.xsdl', 'foo'),
    SoapPayload::forNonWsdl('foo', 'bar', 'fiz'),
]);

it('creates a payload without a SOAP method', function (SoapPayload $payload) {
    $payload->action();
})->with([
    SoapPayload::forWsdl('test.xsdl'),
    SoapPayload::forNonWsdl('foo', 'bar'),
])->throws(MissingSoapActionException::class);

it('overrides the SOAP action', function (SoapPayload $payload) {
    expect($payload->action())->toBe('foo');

    $payload->withAction('fiz');

    expect($payload->action())->toBe('fiz');
})->with([
    SoapPayload::forWsdl('test.xsdl', 'foo'),
    SoapPayload::forNonWsdl('foo', 'bar', 'foo'),
]);

it('sets SOAP headers', function (SoapPayload $payload) {
    $headers = [
        new \SoapHeader('http://localhost', 'Foo', 'a'),
        new \SoapHeader('http://localhost', 'Bar', 'b'),
    ];

    $payload->withHeader($headers[0]);
    $payload->withHeader($headers[1]);

    expect($payload->headers())->toBe($headers);
})->with([
    SoapPayload::forWsdl('test.xsdl', 'foo'),
    SoapPayload::forNonWsdl('foo', 'bar', 'foo'),
]);

it('sets HTTP method', function (SoapPayload $payload) {
    expect($payload->httpMethod())->toBe('POST');

    $payload->withHttpMethod('GET');

    expect($payload->httpMethod())->toBe('GET');
})->with([
    SoapPayload::forWsdl('test.xsdl', 'foo'),
    SoapPayload::forNonWsdl('foo', 'bar', 'foo'),
]);
