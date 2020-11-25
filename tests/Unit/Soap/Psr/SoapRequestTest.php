<?php

namespace CuyZ\WebZ\Tests\Unit\Soap\Psr;

use CuyZ\WebZ\Soap\Psr\SoapRequest;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CuyZ\WebZ\Soap\Psr\SoapRequest
 */
class SoapRequestTest extends TestCase
{
    public function test_creation()
    {
        $request = new SoapRequest(
            'foo',
            'bar',
            123,
            'fiz'
        );

        self::assertSame('foo', $request->endpoint());
        self::assertSame('bar', $request->soapAction());
        self::assertSame(123, $request->soapVersion());
        self::assertSame('fiz', $request->soapMessage());
    }
}
