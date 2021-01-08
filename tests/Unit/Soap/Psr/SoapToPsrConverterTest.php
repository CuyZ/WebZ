<?php

namespace CuyZ\WebZ\Tests\Unit\Soap\Psr;

use CuyZ\WebZ\Soap\Psr\SoapToPsrConverter;
use CuyZ\WebZ\Soap\SoapPayload;
use CuyZ\WebZ\Tests\Fixture\Soap\Server\FakeSoapServerClass;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use SoapFault;

/**
 * @covers \CuyZ\WebZ\Soap\Psr\SoapToPsrConverter
 */
class SoapToPsrConverterTest extends TestCase
{
    public function test_create_request_in_soap_1_1()
    {
        $payload = SoapPayload::forWsdl(FakeSoapServerClass::WSDL_FILE)
            ->withSoapVersion11()
            ->withHttpMethod('POST')
            ->withAction('returnValue')
            ->withArguments([['foo' => 'bar']]);

        $converter = new SoapToPsrConverter($payload);

        $request = $converter->toRequest();

        self::assertSame('POST', $request->getMethod());
        self::assertSame('/soap', $request->getRequestTarget());
        self::assertSame('http://localhost:8080/soap', (string)$request->getUri());

        self::assertSame(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://localhost:8080/soap" xmlns:ns2="http://xml.apache.org/xml-soap" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><SOAP-ENV:Body><ns1:returnValue><value SOAP-ENC:arrayType="ns2:Map[1]" xsi:type="SOAP-ENC:Array"><item xsi:type="ns2:Map"><item><key xsi:type="xsd:string">foo</key><value xsi:type="xsd:string">bar</value></item></item></value></ns1:returnValue></SOAP-ENV:Body></SOAP-ENV:Envelope>

XML,
            $request->getBody()->getContents()
        );

        self::assertSame(
            [
                'Host' => ['localhost:8080'],
                'Content-Length' => ['705'],
                'SOAPAction' => ['http://localhost:8080/soap#returnValue'],
                'Content-Type' => ['text/xml; charset="utf-8"'],
            ],
            $request->getHeaders()
        );
    }

    public function test_create_request_in_soap_1_2()
    {
        $payload = SoapPayload::forWsdl(FakeSoapServerClass::WSDL_FILE)
            ->withSoapVersion12()
            ->withHttpMethod('POST')
            ->withAction('returnValue')
            ->withArguments([['foo' => 'bar']]);

        $converter = new SoapToPsrConverter($payload);

        $request = $converter->toRequest();

        self::assertSame('POST', $request->getMethod());
        self::assertSame('/soap', $request->getRequestTarget());
        self::assertSame('http://localhost:8080/soap', (string)$request->getUri());

        self::assertSame(
            <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<env:Envelope xmlns:env="http://www.w3.org/2003/05/soap-envelope" xmlns:ns1="http://localhost:8080/soap" xmlns:ns2="http://xml.apache.org/xml-soap" xmlns:enc="http://www.w3.org/2003/05/soap-encoding" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema"><env:Body><ns1:returnValue env:encodingStyle="http://www.w3.org/2003/05/soap-encoding"><value enc:itemType="ns2:Map" enc:arraySize="1" xsi:type="enc:Array"><item xsi:type="ns2:Map"><item><key xsi:type="xsd:string">foo</key><value xsi:type="xsd:string">bar</value></item></item></value></ns1:returnValue></env:Body></env:Envelope>

XML,
            $request->getBody()->getContents()
        );

        self::assertSame(
            [
                'Host' => ['localhost:8080'],
                'Content-Length' => ['668'],
                'Content-Type' => ['application/soap+xml; charset="utf-8"; action="http://localhost:8080/soap#returnValue"'],
            ],
            $request->getHeaders()
        );
    }

    public function test_create_request_in_soap_1_2_with_HTTP_NOT_POST()
    {
        $payload = SoapPayload::forWsdl(FakeSoapServerClass::WSDL_FILE)
            ->withSoapVersion12()
            ->withHttpMethod('PUT')
            ->withAction('returnValue')
            ->withArguments([['foo' => 'bar']]);

        $converter = new SoapToPsrConverter($payload);

        $request = $converter->toRequest();

        self::assertSame('PUT', $request->getMethod());
        self::assertSame('/soap', $request->getRequestTarget());
        self::assertSame('http://localhost:8080/soap', (string)$request->getUri());
        self::assertSame('', $request->getBody()->getContents());

        self::assertSame(
            [
                'Host' => ['localhost:8080'],
                'Accept' => ['application/soap+xml'],
            ],
            $request->getHeaders()
        );
    }

    public function test_create_response()
    {
        $payload = SoapPayload::forWsdl(FakeSoapServerClass::WSDL_FILE)
            ->withSoapVersion11()
            ->withHttpMethod('POST')
            ->withAction('returnValue')
            ->withArguments([['foo' => 'bar']]);

        $converter = new SoapToPsrConverter($payload);

        $responseXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://localhost:8080/soap" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:ns2="http://xml.apache.org/xml-soap" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><SOAP-ENV:Body><ns1:returnValueResponse><return xsi:type="ns2:Map"><item><key xsi:type="xsd:string">foo</key><value xsi:type="xsd:string">bar</value></item></return></ns1:returnValueResponse></SOAP-ENV:Body></SOAP-ENV:Envelope>

XML;

        $response = new Response(200, ['Content-Type' => ['application/soap+xml']], $responseXml);

        $raw = $converter->fromResponse($response);

        self::assertSame(['foo' => 'bar'], $raw);
    }

    public function test_converts_soap_fault_response()
    {
        $this->expectException(SoapFault::class);

        $payload = SoapPayload::forWsdl(FakeSoapServerClass::WSDL_FILE)
            ->withSoapVersion11()
            ->withHttpMethod('POST')
            ->withAction('returnValue')
            ->withArguments([['foo' => 'bar']]);

        $converter = new SoapToPsrConverter($payload);

        $responseXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"><SOAP-ENV:Body><SOAP-ENV:Fault><faultcode>foo</faultcode><faultstring>bar</faultstring></SOAP-ENV:Fault></SOAP-ENV:Body></SOAP-ENV:Envelope>

XML;

        $response = new Response(200, ['Content-Type' => ['application/soap+xml']], $responseXml);

        $converter->fromResponse($response);
    }
}
