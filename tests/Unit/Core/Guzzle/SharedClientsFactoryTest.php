<?php

namespace CuyZ\WebZ\Tests\Unit\Core\Guzzle;

use CuyZ\WebZ\Core\Guzzle\SharedClientsFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CuyZ\WebZ\Core\Guzzle\SharedClientsFactory
 */
class SharedClientsFactoryTest extends TestCase
{
    public function test_returns_a_new_client_instance_if_async_call_hash_is_null()
    {
        $factory = new SharedClientsFactory();

        $client1 = $factory->build(null);
        $client2 = $factory->build(null);

        self::assertNotSame($client1, $client2);
    }

    public function test_returns_a_singleton_for_a_specific_async_call_hash()
    {
        $factory = new SharedClientsFactory();

        $clientFoo1 = $factory->build('foo');
        $clientBar1 = $factory->build('bar');
        $clientFoo2 = $factory->build('foo');
        $clientBar2 = $factory->build('bar');

        self::assertSame($clientFoo1, $clientFoo2);
        self::assertSame($clientBar1, $clientBar2);
    }
}
