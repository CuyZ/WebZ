<?php

namespace CuyZ\WebZ\Tests\Integration\WithServer;

use CuyZ\WebZ\Core\Bus\Bus;
use CuyZ\WebZ\Soap\SoapPayload;
use CuyZ\WebZ\Soap\SoapTransport;
use CuyZ\WebZ\Tests\Fixture\FakeResult;
use CuyZ\WebZ\Tests\Fixture\Soap\Server\FakeSoapServerClass;
use CuyZ\WebZ\Tests\Fixture\Soap\WebService\TestReturnsInputWebService;
use CuyZ\WebZ\Tests\Fixture\Soap\WebService\TestThrowsSoapFaultWebService;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyWrapResultWebService;
use CuyZ\WebZ\Tests\Integration\ServerTestCase;
use DateTime;
use DateTimeZone;
use SoapFault;
use stdClass;

class SoapTest extends ServerTestCase
{
    public function test_throws_a_SoapFault()
    {
        $this->expectException(SoapFault::class);

        $bus = Bus::builder()
            ->withTransport(new SoapTransport())
            ->build();

        $webservice = new TestThrowsSoapFaultWebService('foo', 'bar');

        $bus->call($webservice);
    }

    public function soapDataProvider(): array
    {
        return [
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
    }

    /**
     * @dataProvider soapDataProvider
     * @param $input
     * @param array $expectedOutput
     */
    public function test_returns_a_result_parsed_as_an_array_synchronously($input, array $expectedOutput)
    {
        $bus = Bus::builder()
            ->withTransport(new SoapTransport())
            ->build();

        $webservice = new TestReturnsInputWebService($input);

        $result = $bus->call($webservice);

        self::assertSame($expectedOutput, $result);
    }

    /**
     * @dataProvider soapDataProvider
     * @param $input
     * @param array $expectedOutput
     */
    public function test_returns_a_result_parsed_as_an_array_asynchronously($input, array $expectedOutput)
    {
        $bus = Bus::builder()
            ->withTransport(new SoapTransport())
            ->build();

        $webservice = new TestReturnsInputWebService($input);

        $promises = $bus->callAsync($webservice);

        $result = $promises[0]->wait();

        self::assertSame($expectedOutput, $result);
    }

    /**
     * @dataProvider soapDataProvider
     * @param $input
     * @param array $expectedOutput
     */
    public function test_returns_a_wrapped_result_synchronously($input, array $expectedOutput)
    {
        $bus = Bus::builder()
            ->withTransport(new SoapTransport())
            ->build();

        $payload = SoapPayload::forWsdl(FakeSoapServerClass::WSDL_URI, 'returnValue')
            ->withArguments([$input]);

        $webservice = new DummyWrapResultWebService($payload);

        /** @var FakeResult $result */
        $result = $bus->call($webservice);

        self::assertInstanceOf(FakeResult::class, $result);
        self::assertSame($expectedOutput, $result->raw);
    }

    /**
     * @dataProvider soapDataProvider
     * @param $input
     * @param array $expectedOutput
     */
    public function test_returns_a_wrapped_result_asynchronously($input, array $expectedOutput)
    {
        $bus = Bus::builder()
            ->withTransport(new SoapTransport())
            ->build();

        $payload = SoapPayload::forWsdl(FakeSoapServerClass::WSDL_URI, 'returnValue')
            ->withArguments([$input]);

        $webservice = new DummyWrapResultWebService($payload);

        $promises = $bus->callAsync($webservice);

        /** @var FakeResult $result */
        $result = $promises[0]->wait();

        self::assertInstanceOf(FakeResult::class, $result);
        self::assertSame($expectedOutput, $result->raw);
    }

    public function test_contains_the_request_and_response_traces()
    {
        $transport = new SoapTransport();
        $webservice = new TestReturnsInputWebService('bar');

        $result = $transport->send($webservice->getPayload());

        self::assertSame(
            <<<REQUEST
POST /soap HTTP/1.1
Host: localhost:8080
Content-Length: 508
SOAPAction: http://localhost:8080/soap#returnValue
Content-Type: text/xml; charset="utf-8"

<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://localhost:8080/soap" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><SOAP-ENV:Body><ns1:returnValue><value xsi:type="xsd:string">bar</value></ns1:returnValue></SOAP-ENV:Body></SOAP-ENV:Envelope>

REQUEST,
            $result->requestTrace()
        );

        $date = (new DateTime('now', new DateTimeZone('GMT')))->format('r');
        $date = str_replace('+0000', 'GMT', $date);

        self::assertSame(
            <<<RESPONSE
HTTP/1.1 200 OK
Server: ReactPHP/1
Date: $date
Content-Length: 526
Connection: close

<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://localhost:8080/soap" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><SOAP-ENV:Body><ns1:returnValueResponse><return xsi:type="xsd:string">bar</return></ns1:returnValueResponse></SOAP-ENV:Body></SOAP-ENV:Envelope>

RESPONSE,
            $result->responseTrace()
        );
    }
}
