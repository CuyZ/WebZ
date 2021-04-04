<?php

namespace CuyZ\WebZ\Tests\Unit\Core\Guzzle;

use CuyZ\WebZ\Core\Guzzle\ClosureFactory;
use CuyZ\WebZ\Core\Guzzle\HttpClient;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CuyZ\WebZ\Core\Guzzle\ClosureFactory
 */
class ClosureFactoryTest extends TestCase
{
    public function test_executes_the_internal_closure()
    {
        $receivedHash = null;
        $client = new HttpClient();

        $factory = new ClosureFactory(function (?string $asyncCallHash) use ($client, &$receivedHash) {
            $receivedHash = $asyncCallHash;

            return $client;
        });

        $actualClient = $factory->build('foo');

        self::assertSame($client, $actualClient);
        self::assertSame('foo', $receivedHash);
    }
}
