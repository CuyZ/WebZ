<?php

namespace CuyZ\WebZ\Tests\Unit\Core;

use CuyZ\WebZ\Core\Exception\NotAsyncCallException;
use CuyZ\WebZ\Core\Exception\PayloadGroupHashAlreadySetException;
use CuyZ\WebZ\Core\WebService;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyCustomPayloadHashWebService;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyRandomPayloadWebService;
use PHPUnit\Framework\TestCase;

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

    public function test_sets_payload_group_hash()
    {
        $webService = new DummyRandomPayloadWebService();

        $webService->setPayloadGroupHash('foo');

        self::assertSame('foo', $webService->getPayloadGroupHash());
    }

    public function test_throws_on_unset_payload_group_hash()
    {
        $this->expectException(NotAsyncCallException::class);

        $webService = new DummyRandomPayloadWebService();

        $webService->getPayloadGroupHash();
    }

    public function test_knows_if_it_is_an_async_call()
    {
        $webService = new DummyRandomPayloadWebService();

        self::assertFalse($webService->isAsyncCall());

        $webService->setPayloadGroupHash('foo');

        self::assertTrue($webService->isAsyncCall());
    }

    public function test_throws_if_payload_group_hash_is_overridden()
    {
        $this->expectException(PayloadGroupHashAlreadySetException::class);

        $webService = new DummyRandomPayloadWebService();

        $webService->setPayloadGroupHash('foo');
        $webService->setPayloadGroupHash('foo');
    }
}
