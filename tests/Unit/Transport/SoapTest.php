<?php

use CuyZ\WebZ\Core\Result\RawResult;
use CuyZ\WebZ\Soap\Exception\MissingSoapActionException;
use CuyZ\WebZ\Soap\SoapPayload;
use CuyZ\WebZ\Soap\SoapTransport;
use CuyZ\WebZ\Tests\Fixture\Soap\FakeSoapSender;

it('returns null for an incompatible payload', function () {
    $transport = new SoapTransport();

    expect($transport->send(new stdClass()))->toBeNull();
    expect($transport->sendAsync(new stdClass(), null))->toBeNull();
});

it('returns a soap result', function (SoapPayload $payload, array $data, ?string $exceptionClass) {
    $responses = [
        'ok' => 'foo',
        'err' => new SoapFault('foo', 'bar'),
    ];

    $transport = new SoapTransport(new FakeSoapSender($responses));

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
    $transport = new SoapTransport(new FakeSoapSender());

    $transport->send($payload);
})->with([
    SoapPayload::forNonWsdl('test', 'test'),
    SoapPayload::forWsdl('test.wsdl'),
])->throws(MissingSoapActionException::class);
