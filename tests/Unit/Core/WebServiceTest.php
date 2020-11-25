<?php

namespace CuyZ\WebZ\Tests\Unit\Core;

use CuyZ\WebZ\Core\Exception\NotAsyncCallException;
use CuyZ\WebZ\Core\Exception\AsyncCallHashAlreadySetException;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyCustomPayloadHashWebService;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyRandomCustomPayloadHashWebService;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyRandomPayloadWebService;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CuyZ\WebZ\Core\WebService
 */
class WebServiceTest extends TestCase
{
    public function test_payload_is_memoized()
    {
        $webService = new DummyRandomPayloadWebService();

        $payload1 = $webService->getPayload();
        $payload2 = $webService->getPayload();

        self::assertSame($payload1, $payload2);
    }

    public function test_can_have_a_custom_hash()
    {
        $webService = new DummyCustomPayloadHashWebService('foo');

        self::assertSame('foo', $webService->getPayloadHash());
    }

    public function test_default_payload_hash_is_memoized()
    {
        $webService = new DummyRandomPayloadWebService();

        $hash1 = $webService->getPayloadHash();
        $hash2 = $webService->getPayloadHash();

        self::assertSame($hash1, $hash2);
    }

    public function test_custom_payload_hash_is_memoized()
    {
        $webService = new DummyRandomCustomPayloadHashWebService();

        $hash1 = $webService->getPayloadHash();
        $hash2 = $webService->getPayloadHash();

        self::assertSame($hash1, $hash2);
    }

    public function test_sets_async_call_hash()
    {
        $webService = new DummyRandomPayloadWebService();

        $webService->markAsAsyncCall('foo');

        self::assertSame('foo', $webService->getAsyncCallHash());
    }

    public function test_throws_on_unset_async_call_hash()
    {
        $this->expectException(NotAsyncCallException::class);

        $webService = new DummyRandomPayloadWebService();

        $webService->getAsyncCallHash();
    }

    public function test_knows_if_it_is_an_async_call()
    {
        $webService = new DummyRandomPayloadWebService();

        self::assertFalse($webService->isAsyncCall());

        $webService->markAsAsyncCall('foo');

        self::assertTrue($webService->isAsyncCall());
    }

    public function test_throws_if_async_call_hash_is_overridden()
    {
        $this->expectException(AsyncCallHashAlreadySetException::class);

        $webService = new DummyRandomPayloadWebService();

        $webService->markAsAsyncCall('foo');
        $webService->markAsAsyncCall('foo');
    }
}
