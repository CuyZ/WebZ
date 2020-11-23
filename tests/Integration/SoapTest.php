<?php
/** @noinspection PhpComposerExtensionStubsInspection */

use CuyZ\WebZ\Core\Bus\Bus;
use CuyZ\WebZ\Soap\SoapPayload;
use CuyZ\WebZ\Soap\SoapTransport;
use CuyZ\WebZ\Tests\Fixture\FakeResult;
use CuyZ\WebZ\Tests\Fixture\Soap\Server\FakeSoapServerClass;
use CuyZ\WebZ\Tests\Fixture\Soap\WebService\TestReturnsInputWebService;
use CuyZ\WebZ\Tests\Fixture\Soap\WebService\TestThrowsSoapFaultWebService;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyWrapResultWebService;

dataset('soap', function () {
    $sets = [
        [
            'input' => 'foo',
            'expected' => ['value' => 'foo'],
        ],
        [
            'input' => 123,
            'expected' => ['value' => 123],
        ],
        [
            'input' => 123.456,
            'expected' => ['value' => 123.456],
        ],
        [
            'input' => true,
            'expected' => ['value' => true],
        ],
        [
            'input' => false,
            'expected' => ['value' => false],
        ],
        [
            'input' => null,
            'expected' => ['value' => null],
        ],
        [
            'input' => new stdClass(),
            'expected' => [],
        ],
        [
            'input' => ['foo' => 'bar'],
            'expected' => ['foo' => 'bar'],
        ],
        [
            'input' => ['foo' => ['bar' => 'fiz']],
            'expected' => ['foo' => ['bar' => 'fiz']],
        ],
        [
            'input' => (function () {
                $raw = new stdClass();
                $raw->foo = [];
                $raw->foo['bar'] = new stdClass();
                $raw->foo['bar']->fiz = 'abc';
                return $raw;
            })(),
            'expected' => ['foo' => ['bar' => ['fiz' => 'abc']]],
        ],
    ];

    foreach ($sets as $set) {
        yield $set;
    }
});

it('throws a returned SoapFault', function () {
    $bus = Bus::builder()
        ->withTransport(new SoapTransport())
        ->build();

    $webservice = new TestThrowsSoapFaultWebService('foo', 'bar');

    $bus->call($webservice);
})->throws(SoapFault::class);

it('throws a thrown SoapFault', function () {
    $bus = Bus::builder()
        ->withTransport(new SoapTransport())
        ->build();

    $webservice = new TestThrowsSoapFaultWebService('foo', 'bar');

    $bus->call($webservice);
})->throws(SoapFault::class);

it('returns a result parsed as an array (synchronous)', function ($input, array $expectedOutput) {
    $bus = Bus::builder()
        ->withTransport(new SoapTransport())
        ->build();

    $webservice = new TestReturnsInputWebService($input);

    $result = $bus->call($webservice);

    expect($result)->toBeArray();
    expect($result)->toBe($expectedOutput);
})->with('soap');

it('returns a result parsed as an array (asynchronous)', function ($input, array $expectedOutput) {
    $bus = Bus::builder()
        ->withTransport(new SoapTransport())
        ->build();

    $webservice = new TestReturnsInputWebService($input);

    $promises = $bus->callAsync($webservice);

    $result = $promises[0]->wait();

    expect($result)->toBeArray();
    expect($result)->toBe($expectedOutput);
})->with('soap');

it('returns a wrapped result (synchronous)', function ($input, array $expectedOutput) {
    $bus = Bus::builder()
        ->withTransport(new SoapTransport())
        ->build();

    $payload = SoapPayload::forWsdl(FakeSoapServerClass::WSDL_URI, 'returnValue')
        ->withArguments([$input]);

    $webservice = new DummyWrapResultWebService($payload);

    /** @var FakeResult $result */
    $result = $bus->call($webservice);

    expect($result)->toBeInstanceOf(FakeResult::class);
    expect($result->raw)->toBe($expectedOutput);
})->with('soap');

it('returns a wrapped result (asynchronous)', function ($input, array $expectedOutput) {
    $bus = Bus::builder()
        ->withTransport(new SoapTransport())
        ->build();

    $payload = SoapPayload::forWsdl(FakeSoapServerClass::WSDL_URI, 'returnValue')
        ->withArguments([$input]);

    $webservice = new DummyWrapResultWebService($payload);

    $promises = $bus->callAsync($webservice);

    /** @var FakeResult $result */
    $result = $promises[0]->wait();

    expect($result)->toBeInstanceOf(FakeResult::class);
    expect($result->raw)->toBe($expectedOutput);
})->with('soap');

it('contains the xml request and response strings', function () {
    $client = null;

    $transport = new SoapTransport();

    $webservice = new TestReturnsInputWebService('bar');

    $result = $transport->send($webservice->getPayload());

    expect($result->requestTrace())->toBeString()->not->toBeEmpty();
    expect($result->requestTrace())->toEqual(<<<REQUEST
POST /soap HTTP/1.1
Host: localhost:8080
Content-Length: 508
SOAPAction: http://localhost:8080/soap#returnValue
Content-Type: text/xml; charset="utf-8"

<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://localhost:8080/soap" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><SOAP-ENV:Body><ns1:returnValue><value xsi:type="xsd:string">bar</value></ns1:returnValue></SOAP-ENV:Body></SOAP-ENV:Envelope>

REQUEST
    );

    expect($result->responseTrace())->toBeString()->not->toBeEmpty();

    $date = (new DateTime('now', new DateTimeZone('GMT')))->format('r');
    $date = str_replace('+0000', 'GMT', $date);

    expect($result->responseTrace())->toEqual(<<<RESPONSE
HTTP/1.1 200 OK
Server: ReactPHP/1
Date: $date
Content-Length: 526
Connection: close

<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://localhost:8080/soap" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><SOAP-ENV:Body><ns1:returnValueResponse><return xsi:type="xsd:string">bar</return></ns1:returnValueResponse></SOAP-ENV:Body></SOAP-ENV:Envelope>

RESPONSE
    );
});
