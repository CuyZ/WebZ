<?php

namespace CuyZ\WebZ\Tests\Unit\Core\Http;

use CuyZ\WebZ\Core\Http\ClosureFactory;
use CuyZ\WebZ\Core\Http\HttpClient;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CuyZ\WebZ\Core\Http\ClosureFactory
 */
class ClosureFactoryTest extends TestCase
{
    public function test_executes_the_internal_closure()
    {
        $receivedHash = null;
        $client = HttpClient::create();

        $factory = new ClosureFactory(function (?string $asyncCallHash) use ($client, &$receivedHash) {
            $receivedHash = $asyncCallHash;

            return $client;
        });

        $actualClient = $factory->build('foo');

        self::assertSame($client, $actualClient);
        self::assertSame('foo', $receivedHash);
    }
}
