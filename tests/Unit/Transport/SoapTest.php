<?php

use CuyZ\WebZ\Core\Result\RawResult;
use CuyZ\WebZ\Soap\ClientFactory;
use CuyZ\WebZ\Soap\Exception\MissingSoapMethodException;
use CuyZ\WebZ\Soap\SoapPayload;
use CuyZ\WebZ\Soap\SoapTransport;
use CuyZ\WebZ\Tests\Fixture\Soap\Client\UnitTestSoapClient;

it('returns null for an incompatible payload', function ($factory) {
    $transport = new SoapTransport($factory);

    expect($transport->send(new stdClass()))->toBeNull();
})->with([
    null,

    fn(SoapPayload $payload) => new UnitTestSoapClient(),

    new class implements ClientFactory {
        public function build(SoapPayload $payload): SoapClient
        {
            return new UnitTestSoapClient();
        }
    },
]);

it('returns a soap result', function (SoapPayload $payload, array $data, ?string $exceptionClass) {
    $responses = [
        'ok' => 'foo',
        'err' => new SoapFault('foo', 'bar'),
    ];

    $transport = new SoapTransport(fn(SoapPayload $payload) => new UnitTestSoapClient($responses));

    $raw = $transport->send($payload);

    expect($raw)->toBeInstanceOf(RawResult::class);
    expect($raw->data())->toBe($data);

    if ($exceptionClass) {
        expect($raw->exception())->toBeInstanceOf($exceptionClass);
    } else {
        expect($raw->exception())->toBeNull();
    }
})->with([
    [
        'payload' => SoapPayload::forNonWsdl('test', 'test', 'ok'),
        'data' => ['value' => 'foo'],
        'exception' => null,
    ],
    [
        'payload' => SoapPayload::forWsdl('test.wsdl', 'ok'),
        'data' => ['value' => 'foo'],
        'exception' => null,
    ],

    [
        'payload' => SoapPayload::forNonWsdl('test', 'test', 'err'),
        'data' => ['value' => 'bar'],
        'exception' => SoapFault::class,
    ],
    [
        'payload' => SoapPayload::forWsdl('test.wsdl', 'err'),
        'data' => ['value' => 'bar'],
        'exception' => SoapFault::class,
    ],
]);

it('throws on un-configured SOAP method', function (SoapPayload $payload) {
    $transport = new SoapTransport(fn(SoapPayload $payload) => new UnitTestSoapClient());

    $transport->send($payload);
})->with([
    SoapPayload::forNonWsdl('test', 'test'),
    SoapPayload::forWsdl('test.wsdl'),
])->throws(MissingSoapMethodException::class);
