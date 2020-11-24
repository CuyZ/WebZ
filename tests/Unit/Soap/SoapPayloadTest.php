<?php

namespace CuyZ\WebZ\Tests\Unit\Soap;

use CuyZ\WebZ\Soap\Exception\MissingSoapActionException;
use CuyZ\WebZ\Soap\SoapPayload;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CuyZ\WebZ\Soap\SoapPayload
 */
class SoapPayloadTest extends TestCase
{
    public function test_creation_in_wsdl_mode()
    {
        $payload = SoapPayload::forWsdl('test.wsdl', 'foo');

        self::assertSame('test.wsdl', $payload->wsdl());
        self::assertSame('foo', $payload->action());
        self::assertSame(SoapPayload::DEFAULT_OPTIONS, $payload->options());
    }

    public function test_creation_in_non_wsdl_mode()
    {
        $payload = SoapPayload::forNonWsdl('foo', 'bar', 'fiz');

        self::assertNull($payload->wsdl());
        self::assertSame('fiz', $payload->action());

        self::assertSame(
            SoapPayload::DEFAULT_OPTIONS + [
                'location' => 'foo',
                'uri' => 'bar',
            ],
            $payload->options()
        );
    }

    public function test_location_and_uri_cannot_be_overridden_from_options_in_non_wsdl_mode()
    {
        $payload = SoapPayload::forNonWsdl('foo', 'bar', 'fiz');

        $payload->withOptions([
            'location' => 'abc',
            'uri' => 'def',
        ]);

        self::assertSame(
            [
                'location' => 'foo',
                'uri' => 'bar',
            ],
            $payload->options()
        );
    }

    public function test_overrides_default_options()
    {
        $payload = SoapPayload::forWsdl('test.wsdl', 'foo');

        $payload->withOptions([]);

        self::assertSame([], $payload->options());
    }

    public function payloadsDataProvider()
    {
        return [
            [SoapPayload::forWsdl('test.wsdl', 'foo')],
            [SoapPayload::forNonWsdl('foo', 'bar', 'fiz')],
        ];
    }

    /**
     * @dataProvider payloadsDataProvider
     * @param SoapPayload $payload
     */
    public function test_has_empty_arguments_by_default(SoapPayload $payload)
    {
        self::assertEmpty($payload->arguments());
    }

    /**
     * @dataProvider payloadsDataProvider
     * @param SoapPayload $payload
     */
    public function test_sets_the_location(SoapPayload $payload)
    {
        $payload->withLocation('abcd');

        $options = $payload->options();

        self::assertArrayHasKey('location', $options);
        self::assertSame('abcd', $options['location']);
    }

    /**
     * @dataProvider payloadsDataProvider
     * @param SoapPayload $payload
     */
    public function test_sets_the_call_arguments(SoapPayload $payload)
    {
        $payload->withArguments(['foo', 'bar']);

        self::assertSame(['foo', 'bar'], $payload->arguments());
    }

    public function test_overrides_the_action()
    {
        $payload = SoapPayload::forWsdl('test.wsdl');

        $payload->withAction('bar');

        self::assertSame('bar', $payload->action());
    }

    public function test_throws_on_missing_action()
    {
        $this->expectException(MissingSoapActionException::class);

        SoapPayload::forWsdl('test.wsdl')->action();
    }

    public function test_set_headers()
    {
        $headers = [
            new \SoapHeader("http://localhost", "Foo", "a"),
            new \SoapHeader("http://localhost", "Bar", "b"),
        ];

        $payload = SoapPayload::forWsdl('test.wsdl');

        $payload->withHeader($headers[0]);
        $payload->withHeader($headers[1]);

        self::assertSame($headers, $payload->headers());
    }

    public function test_set_HTTP_method()
    {
        $payload = SoapPayload::forWsdl('test.wsdl');

        self::assertSame('POST', $payload->httpMethod());

        $payload->withHttpMethod('GET');

        self::assertSame('GET', $payload->httpMethod());
    }
}
