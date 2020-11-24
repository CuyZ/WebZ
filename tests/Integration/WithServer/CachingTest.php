<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Tests\Integration\WithServer;

use Cache\Adapter\PHPArray\ArrayCachePool;
use Cache\Adapter\Void\VoidCachePool;
use CuyZ\WebZ\Core\Bus\Bus;
use CuyZ\WebZ\Core\WebService;
use CuyZ\WebZ\Http\HttpTransport;
use CuyZ\WebZ\Soap\SoapTransport;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyCacheWebService;
use CuyZ\WebZ\Tests\Integration\ServerTestCase;

/**
 * @coversNothing
 */
final class CachingTest extends ServerTestCase
{
    public function transportsDataProvider(): array
    {
        return [
            [
                DummyCacheWebService::soap('foo', 10),
            ],
            [
                DummyCacheWebService::http('bar', 10),
            ],
        ];
    }

    /**
     * @dataProvider transportsDataProvider
     * @param WebService $webService
     */
    public function test_returns_a_memoized_result(WebService $webService)
    {
        $bus = Bus::builder()
            ->withTransport(new SoapTransport())
            ->withTransport(new HttpTransport())
            ->withCache(new VoidCachePool())
            ->build();

        $result1 = $bus->call($webService);
        $result2 = $bus->call($webService);

        self::assertSame($result1, $result2);
    }

    /**
     * @dataProvider transportsDataProvider
     * @param WebService $webService
     */
    public function test_returns_a_new_result_for_each_call(WebService $webService)
    {
        $bus = Bus::builder()
            ->withTransport(new SoapTransport())
            ->withTransport(new HttpTransport())
            ->withCache(new VoidCachePool())
            ->withoutMemoization()
            ->build();

        $result1 = $bus->call($webService);
        $result2 = $bus->call($webService);

        self::assertNotSame($result1, $result2);
    }

    /**
     * @dataProvider transportsDataProvider
     * @param WebService $webService
     */
    public function test_returns_a_cached_result(WebService $webService)
    {
        $pool = new ArrayCachePool();

        $bus = Bus::builder()
            ->withTransport(new SoapTransport())
            ->withTransport(new HttpTransport())
            ->withCache($pool)
            ->build();

        $result1 = $bus->call($webService);

        self::assertTrue($pool->has($webService->getPayloadHash()));

        $result2 = $bus->call($webService);

        self::assertSame($result1, $result2);
    }
}
