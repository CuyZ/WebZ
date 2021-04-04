<?php

namespace CuyZ\WebZ\Tests\Unit\Core\Http;

use CuyZ\WebZ\Core\Http\HttpClient;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CuyZ\WebZ\Core\Http\HttpClient
 */
class HttpClientTest extends TestCase
{
    public function test_returns_a_new_client_instance_if_async_call_hash_is_null()
    {
        $client1 = HttpClient::create();
        $client2 = HttpClient::create();

        self::assertNotSame($client1, $client2);
    }

    public function test_returns_a_singleton_for_a_specific_async_call_hash()
    {
        $clientFoo1 = HttpClient::create('foo');
        $clientBar1 = HttpClient::create('bar');
        $clientFoo2 = HttpClient::create('foo');
        $clientBar2 = HttpClient::create('bar');

        self::assertSame($clientFoo1, $clientFoo2);
        self::assertSame($clientBar1, $clientBar2);
    }
}
