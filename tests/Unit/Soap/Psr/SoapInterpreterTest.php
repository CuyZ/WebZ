<?php

namespace CuyZ\WebZ\Tests\Unit\Soap\Psr;

use CuyZ\WebZ\Soap\Psr\SoapInterpreter;
use CuyZ\WebZ\Soap\SoapPayload;
use CuyZ\WebZ\Tests\Fixture\Soap\Server\FakeSoapServerClass;
use PHPUnit\Framework\TestCase;
use SoapFault;

/**
 * @covers \CuyZ\WebZ\Soap\Psr\SoapInterpreter
 */
class SoapInterpreterTest extends TestCase
{
    public function test_interpret_request()
    {
        $payload = SoapPayload::forWsdl(FakeSoapServerClass::WSDL_FILE)
            ->withAction('returnValue')
            ->withArguments([['foo' => 'bar']]);

        $interpreter = new SoapInterpreter($payload);

        $request = $interpreter->request();

        $expectedXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://localhost:8080/soap" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:ns2="http://xml.apache.org/xml-soap" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><SOAP-ENV:Body><ns1:returnValue><value xsi:type="ns2:Map"><item><key xsi:type="xsd:string">foo</key><value xsi:type="xsd:string">bar</value></item></value></ns1:returnValue></SOAP-ENV:Body></SOAP-ENV:Envelope>

XML;

        self::assertSame('http://localhost:8080/soap', $request->endpoint());
        self::assertSame('http://localhost:8080/soap#returnValue', $request->soapAction());
        self::assertSame(1, $request->soapVersion());
        self::assertSame($expectedXml, $request->soapMessage());
    }

    public function test_interprets_response()
    {
        $payload = SoapPayload::forWsdl(FakeSoapServerClass::WSDL_FILE)
            ->withAction('returnValue')
            ->withArguments([['foo' => 'bar']]);

        $interpreter = new SoapInterpreter($payload);

        $responseXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://localhost:8080/soap" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:ns2="http://xml.apache.org/xml-soap" xmlns:SOAP-ENC="http://schemas.xmlsoap.org/soap/encoding/" SOAP-ENV:encodingStyle="http://schemas.xmlsoap.org/soap/encoding/"><SOAP-ENV:Body><ns1:returnValueResponse><return xsi:type="ns2:Map"><item><key xsi:type="xsd:string">foo</key><value xsi:type="xsd:string">bar</value></item></return></ns1:returnValueResponse></SOAP-ENV:Body></SOAP-ENV:Envelope>

XML;

        $response = $interpreter->response($responseXml);

        self::assertSame(['foo' => 'bar'], $response);
    }

    public function test_interpret_soap_fault_response()
    {
        $this->expectException(SoapFault::class);

        $payload = SoapPayload::forWsdl(FakeSoapServerClass::WSDL_FILE)
            ->withAction('throwSoapFault')
            ->withArguments(['foo', 'bar']);

        $interpreter = new SoapInterpreter($payload);

        $responseXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/"><SOAP-ENV:Body><SOAP-ENV:Fault><faultcode>foo</faultcode><faultstring>bar</faultstring></SOAP-ENV:Fault></SOAP-ENV:Body></SOAP-ENV:Envelope>

XML;

        $interpreter->response($responseXml);
    }
}
