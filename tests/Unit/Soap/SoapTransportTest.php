<?php

namespace CuyZ\WebZ\Tests\Unit\Soap;

use CuyZ\WebZ\Core\Result\RawResult;
use CuyZ\WebZ\Soap\Exception\MissingSoapActionException;
use CuyZ\WebZ\Soap\SoapPayload;
use CuyZ\WebZ\Soap\SoapTransport;
use CuyZ\WebZ\Tests\Fixture\Soap\FakeSoapSender;
use PHPUnit\Framework\TestCase;
use SoapFault;
use stdClass;

class SoapTransportTest extends TestCase
{
    public function test_returns_null_for_an_incompatible_payload()
    {
        $transport = new SoapTransport();

        $result = $transport->send(new stdClass());
        $resultAsync = $transport->sendAsync(new stdClass(), null);

        self::assertNull($result);
        self::assertNull($resultAsync);
    }

    public function soapDataProvider(): array
    {
        return [
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
        ];
    }

    /**
     * @dataProvider soapDataProvider
     * @param SoapPayload $payload
     * @param array $data
     * @param string|null $exceptionClass
     */
    public function test_returns_a_soap_result_synchronously(SoapPayload $payload, array $data, ?string $exceptionClass)
    {
        $responses = [
            'ok' => 'foo',
            'err' => new SoapFault('foo', 'bar'),
        ];

        $transport = new SoapTransport(new FakeSoapSender($responses));

        $raw = $transport->send($payload);

        self::assertInstanceOf(RawResult::class, $raw);
        self::assertSame($data, $raw->data());

        if ($exceptionClass) {
            self::assertInstanceOf($exceptionClass, $raw->exception());
        } else {
            self::assertNull($raw->exception());
        }
    }

    /**
     * @dataProvider soapDataProvider
     * @param SoapPayload $payload
     * @param array $data
     * @param string|null $exceptionClass
     */
    public function test_returns_a_soap_result_asynchronously(SoapPayload $payload, array $data, ?string $exceptionClass)
    {
        $responses = [
            'ok' => 'foo',
            'err' => new SoapFault('foo', 'bar'),
        ];

        $transport = new SoapTransport(new FakeSoapSender($responses));

        /** @var RawResult $raw */
        $raw = $transport->sendAsync($payload, null)->wait();

        self::assertInstanceOf(RawResult::class, $raw);
        self::assertSame($data, $raw->data());

        if ($exceptionClass) {
            self::assertInstanceOf($exceptionClass, $raw->exception());
        } else {
            self::assertNull($raw->exception());
        }
    }

    public function noActionDataProvider(): array
    {
        return [
            [SoapPayload::forNonWsdl('test', 'test')],
            [SoapPayload::forWsdl('test.wsdl')],
        ];
    }

    /**
     * @dataProvider noActionDataProvider
     * @param SoapPayload $payload
     */
    public function test_throws_on_un_configured_SOAP_action(SoapPayload $payload)
    {
        $this->expectException(MissingSoapActionException::class);

        $transport = new SoapTransport(new FakeSoapSender());

        $transport->send($payload);
    }
}
