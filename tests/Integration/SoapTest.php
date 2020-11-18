<?php
/** @noinspection PhpComposerExtensionStubsInspection */

use CuyZ\WebZ\Core\Bus\Bus;
use CuyZ\WebZ\Soap\ClientFactory;
use CuyZ\WebZ\Soap\SoapPayload;
use CuyZ\WebZ\Soap\SoapTransport;
use CuyZ\WebZ\Tests\Fixture\FakeResult;
use CuyZ\WebZ\Tests\Fixture\Soap\Client\IntegrationTestSoapClient;
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

    $factories = [
        fn (SoapPayload $payload) => new SoapClient($payload->wsdl(), $payload->options()),
        new class implements ClientFactory
        {
            public function build(SoapPayload $payload): SoapClient
            {
                return new SoapClient($payload->wsdl(), $payload->options());
            }
        },
        null
    ];

    foreach ($factories as $factory) {
        foreach ($sets as $set) {
            yield ['factory' => $factory] + $set;
        }
    }
});

it('throws a returned SoapFault', function () {
    $bus = Bus::builder()
        ->withTransport(new SoapTransport(IntegrationTestSoapClient::factory([
            'exceptions' => false,
        ])))
        ->build();

    $webservice = new TestThrowsSoapFaultWebService('foo', 'bar');

    $bus->call($webservice);
})->throws(SoapFault::class);

it('throws a thrown SoapFault', function () {
    $bus = Bus::builder()
        ->withTransport(new SoapTransport(IntegrationTestSoapClient::factory([
            'exceptions' => true,
        ])))
        ->build();

    $webservice = new TestThrowsSoapFaultWebService('foo', 'bar');

    $bus->call($webservice);
})->throws(SoapFault::class);

it('returns a result parsed as an array', function ($factory, $input, array $expectedOutput) {
    $bus = Bus::builder()
        ->withTransport(new SoapTransport($factory))
        ->build();

    $webservice = new TestReturnsInputWebService($input);

    $result = $bus->call($webservice);

    expect($result)->toBeArray();
    expect($result)->toBe($expectedOutput);
})->with('soap');

it('returns a wrapped result', function ($factory, $input, array $expectedOutput) {
    $bus = Bus::builder()
        ->withTransport(new SoapTransport($factory))
        ->build();

    $payload = SoapPayload::forWsdl(FakeSoapServerClass::WSDL, 'returnValue')
        ->withArguments([$input]);

    $webservice = new DummyWrapResultWebService($payload);

    /** @var FakeResult $result */
    $result = $bus->call($webservice);

    expect($result)->toBeInstanceOf(FakeResult::class);
    expect($result->raw)->toBe($expectedOutput);
})->with('soap');

it('contains the xml request and response strings', function () {
    $client = null;

    $transport = new SoapTransport(IntegrationTestSoapClient::factory(
        ['trace' => true],
        function (SoapClient $soapClient) use (&$client) {
            $client = $soapClient;
        }
    ));

    $webservice = new TestReturnsInputWebService('bar');

    $result = $transport->send($webservice->getPayload());

    expect($result->requestTrace())->toBeString()->not->toBeEmpty();
    expect($result->requestTrace())->toEqual($client->__getLastRequest());

    expect($result->responseTrace())->toBeString()->not->toBeEmpty();
    expect($result->responseTrace())->toEqual($client->__getLastResponse());
});
