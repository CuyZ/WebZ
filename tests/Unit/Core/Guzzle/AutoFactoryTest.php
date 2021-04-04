<?php

namespace CuyZ\WebZ\Tests\Unit\Core\Guzzle;

use CuyZ\WebZ\Core\Guzzle\AutoFactory;
use CuyZ\WebZ\Core\Guzzle\GuzzleClientFactory;
use CuyZ\WebZ\Core\Guzzle\HttpClient;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CuyZ\WebZ\Core\Guzzle\AutoFactory
 */
class AutoFactoryTest extends TestCase
{
    public function test_creates_shared_clients_factory_when_argument_is_null()
    {
        $factory1 = new AutoFactory();

        $firstFoo = $factory1->build('foo');
        $firstBar = $factory1->build('bar');

        $foo = [];
        $bar = [];

        $foo[] = $factory1->build('foo');
        $foo[] = $factory1->build('foo');

        $bar[] = $factory1->build('bar');
        $bar[] = $factory1->build('bar');

        $factory2 = new AutoFactory();

        $foo[] = $factory2->build('foo');
        $foo[] = $factory2->build('foo');

        $bar[] = $factory2->build('bar');
        $bar[] = $factory2->build('bar');

        foreach ($foo as $client) {
            self::assertSame($firstFoo, $client);
        }

        foreach ($bar as $client) {
            self::assertSame($firstBar, $client);
        }
    }

    public function test_wraps_a_closure()
    {
        $receivedHash = null;
        $client = new HttpClient();

        $factory = new AutoFactory(function (?string $asyncCallHash) use ($client, &$receivedHash) {
            $receivedHash = $asyncCallHash;

            return $client;
        });

        $actualClient = $factory->build('foo');

        self::assertSame($client, $actualClient);
        self::assertSame('foo', $receivedHash);
    }

    public function test_wraps_factory_class()
    {
        $client = new HttpClient();

        $wrapped = new class($client) implements GuzzleClientFactory {
            public ?string $receivedHash = null;
            private HttpClient $client;

            public function __construct(HttpClient $client)
            {
                $this->client = $client;
            }

            public function build(?string $asyncCallHash): HttpClient
            {
                $this->receivedHash = $asyncCallHash;

                return $this->client;
            }
        };

        $factory = new AutoFactory($wrapped);

        $actualClient = $factory->build('foo');

        self::assertSame($client, $actualClient);
        self::assertSame('foo', $wrapped->receivedHash);
    }
}
