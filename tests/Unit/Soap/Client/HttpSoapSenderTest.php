<?php

namespace CuyZ\WebZ\Tests\Unit\Soap\Client;

use CuyZ\WebZ\Core\Http\HttpClient;
use CuyZ\WebZ\Core\Result\RawResult;
use CuyZ\WebZ\Soap\Client\HttpSoapSender;
use CuyZ\WebZ\Soap\SoapPayload;
use CuyZ\WebZ\Tests\Fixture\Soap\Server\FakeSoapServerClass;
use Exception;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use SoapFault;

/**
 * @covers \CuyZ\WebZ\Soap\Client\HttpSoapSender
 */
class HttpSoapSenderTest extends TestCase
{
    public function soapDataProvider()
    {
        $responseXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://localhost:8080/soap" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:ns2="http://xml.apache.org/xml-soap" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><SOAP-ENV:Body><ns1:returnValueResponse><return xsi:type="ns2:Map"><item><key xsi:type="xsd:string">foo</key><value xsi:type="xsd:string">bar</value></item></return></ns1:returnValueResponse></SOAP-ENV:Body></SOAP-ENV:Envelope>

XML;

        $response = new Response(200, ['Content-Type' => ['application/soap+xml']], $responseXml);

        yield [
            'response' => $response,
            'data' => ['foo' => 'bar'],
            'exception' => null,
        ];

        $responseXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"><SOAP-ENV:Body><SOAP-ENV:Fault><faultcode>foo</faultcode><faultstring>bar</faultstring></SOAP-ENV:Fault></SOAP-ENV:Body></SOAP-ENV:Envelope>

XML;

        $response = new Response(200, ['Content-Type' => ['application/soap+xml']], $responseXml);

        yield [
            'response' => $response,
            'data' => ['value' => 'bar'],
            'exception' => SoapFault::class,
        ];

        yield [
            'response' => new Exception('fiz'),
            'data' => ['value' => 'fiz'],
            'exception' => Exception::class,
        ];
    }

    /**
     * @dataProvider soapDataProvider
     * @param ResponseInterface|Exception $response
     * @param array $data
     * @param string|null $exceptionClass
     */
    public function test_returns_a_soap_result($response, array $data, ?string $exceptionClass)
    {
        $sender = new HttpSoapSender(fn() => HttpClient::mock($response));

        $payload = SoapPayload::forWsdl(FakeSoapServerClass::WSDL_FILE, 'returnValue');

        /** @var RawResult $raw */
        $raw = $sender->send($payload, null)->wait();

        self::assertInstanceOf(RawResult::class, $raw);
        self::assertSame($data, $raw->data());

        if ($exceptionClass) {
            self::assertInstanceOf($exceptionClass, $raw->exception());
        } else {
            self::assertNull($raw->exception());
        }
    }
}
