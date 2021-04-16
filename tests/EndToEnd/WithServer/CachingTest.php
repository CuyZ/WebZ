<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Tests\EndToEnd\WithServer;

use CuyZ\WebZ\Core\Bus\WebServiceBus;
use CuyZ\WebZ\Core\WebService;
use CuyZ\WebZ\Http\HttpTransport;
use CuyZ\WebZ\Soap\SoapTransport;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyCacheWebService;
use CuyZ\WebZ\Tests\EndToEnd\ServerTestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\Cache\Psr16Cache;

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
        $bus = WebServiceBus::builder()
            ->withTransport(new SoapTransport())
            ->withTransport(new HttpTransport())
            ->withCache(new Psr16Cache(new NullAdapter()))
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
        $bus = WebServiceBus::builder()
            ->withTransport(new SoapTransport())
            ->withTransport(new HttpTransport())
            ->withCache(new Psr16Cache(new NullAdapter()))
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
        $pool = new ArrayAdapter();

        $bus = WebServiceBus::builder()
            ->withTransport(new SoapTransport())
            ->withTransport(new HttpTransport())
            ->withCache(new Psr16Cache($pool))
            ->build();

        $result1 = $bus->call($webService);

        self::assertTrue($pool->hasItem($webService->getPayloadHash()));

        $result2 = $bus->call($webService);

        self::assertSame($result1, $result2);
    }
}
