<?php
/** @noinspection PhpComposerExtensionStubsInspection */

use CuyZ\WebZ\Soap\Exception\MissingSoapMethodException;
use CuyZ\WebZ\Soap\SoapPayload;

it('creates a wsdl mode payload', function () {
    $payload = SoapPayload::forWsdl('test.wsdl', 'foo');

    expect($payload->wsdl())->toBe('test.wsdl');
    expect($payload->method())->toBe('foo');
    expect($payload->options())->toBe(SoapPayload::DEFAULT_OPTIONS);
});

it('creates a non-wsdl mode payload', function () {
    $payload = SoapPayload::forNonWsdl('foo', 'bar', 'fiz');

    expect($payload->wsdl())->toBeNull();
    expect($payload->method())->toBe('fiz');

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
    $payload->method();
})->with([
    SoapPayload::forWsdl('test.xsdl'),
    SoapPayload::forNonWsdl('foo', 'bar'),
])->throws(MissingSoapMethodException::class);

it('overrides the SOAP method', function (SoapPayload $payload) {
    expect($payload->method())->toBe('foo');

    $payload->withMethod('fiz');

    expect($payload->method())->toBe('fiz');
})->with([
    SoapPayload::forWsdl('test.xsdl', 'foo'),
    SoapPayload::forNonWsdl('foo', 'bar', 'foo'),
]);
