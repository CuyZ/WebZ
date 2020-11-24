<?php

namespace CuyZ\WebZ\Tests\Unit\Core\Guzzle;

use CuyZ\WebZ\Core\Guzzle\AutoFactory;
use CuyZ\WebZ\Core\Guzzle\GuzzleClientFactory;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CuyZ\WebZ\Core\Guzzle\AutoFactory
 */
class AutoFactoryTest extends TestCase
{
    public function test_creates_shared_clients_factory_when_argument_is_null()
    {
        $factory = new AutoFactory();

        $clientFoo1 = $factory->build('foo');
        $clientBar1 = $factory->build('bar');
        $clientFoo2 = $factory->build('foo');
        $clientBar2 = $factory->build('bar');

        self::assertSame($clientFoo1, $clientFoo2);
        self::assertSame($clientBar1, $clientBar2);
    }

    public function test_wraps_a_closure()
    {
        $receivedHash = null;
        $client = new Client();

        $factory = new AutoFactory(function (?string $payloadGroupHash) use ($client, &$receivedHash) {
            $receivedHash = $payloadGroupHash;

            return $client;
        });

        $actualClient = $factory->build('foo');

        self::assertSame($client, $actualClient);
        self::assertSame('foo', $receivedHash);
    }

    public function test_wraps_factory_class()
    {
        $client = new Client();

        $wrapped = new class($client) implements GuzzleClientFactory {
            public ?string $receivedHash = null;
            private Client $client;

            public function __construct(Client $client)
            {
                $this->client = $client;
            }

            public function build(?string $payloadGroupHash): Client
            {
                $this->receivedHash = $payloadGroupHash;

                return $this->client;
            }
        };

        $factory = new AutoFactory($wrapped);

        $actualClient = $factory->build('foo');

        self::assertSame($client, $actualClient);
        self::assertSame('foo', $wrapped->receivedHash);
    }
}
