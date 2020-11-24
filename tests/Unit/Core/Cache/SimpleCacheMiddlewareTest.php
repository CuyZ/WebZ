<?php

namespace CuyZ\WebZ\Tests\Unit\Core\Cache;

use Cache\Adapter\PHPArray\ArrayCachePool;
use CuyZ\WebZ\Core\Bus\Pipeline\Next;
use CuyZ\WebZ\Core\Cache\CorruptCacheEntryException;
use CuyZ\WebZ\Core\Cache\SimpleCacheMiddleware;
use CuyZ\WebZ\Core\Result\Result;
use CuyZ\WebZ\Tests\Fixture\Mocks;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyCacheWebService;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyWebService;
use GuzzleHttp\Promise\FulfilledPromise;
use PHPUnit\Framework\TestCase;
use stdClass;

class SimpleCacheMiddlewareTest extends TestCase
{
    public function test_ignores_incompatible_webservices()
    {
        $webservice = new DummyWebService(new stdClass());
        $middleware = new SimpleCacheMiddleware(new ArrayCachePool());

        $result = Mocks::resultOk([1]);

        $next = new Next(function () use (&$result) {
            $result = $result->withData([$result->data()[0] + 1]);

            return new FulfilledPromise($result);
        });

        /** @var Result $result1 */
        $result1 = $middleware->process($webservice, $next)->wait();

        /** @var Result $result2 */
        $result2 = $middleware->process($webservice, $next)->wait();

        self::assertInstanceOf(Result::class, $result1);
        self::assertInstanceOf(Result::class, $result2);

        self::assertSame([2], $result1->data());
        self::assertSame([3], $result2->data());
    }

    public function test_does_not_store_in_cache_if_TTL_is_0()
    {
        $webservice = new DummyCacheWebService(new stdClass(), 0);
        $middleware = new SimpleCacheMiddleware(new ArrayCachePool());

        $result = Mocks::resultOk([1]);

        $next = new Next(function () use (&$result) {
            $result = $result->withData([$result->data()[0] + 1]);

            return new FulfilledPromise($result);
        });

        /** @var Result $result1 */
        $result1 = $middleware->process($webservice, $next)->wait();

        /** @var Result $result2 */
        $result2 = $middleware->process($webservice, $next)->wait();

        self::assertInstanceOf(Result::class, $result1);
        self::assertInstanceOf(Result::class, $result2);

        self::assertSame([2], $result1->data());
        self::assertSame([3], $result2->data());
    }

    public function test_stores_the_result_in_cache_if_TTL_is_greater_than_0()
    {
        $webservice = new DummyCacheWebService(new stdClass(), 10);
        $middleware = new SimpleCacheMiddleware(new ArrayCachePool());

        $result = Mocks::resultOk([1]);

        $next = new Next(function () use (&$result) {
            $result = $result->withData([$result->data()[0] + 1]);

            return new FulfilledPromise($result);
        });

        /** @var Result $result1 */
        $result1 = $middleware->process($webservice, $next)->wait();

        /** @var Result $result2 */
        $result2 = $middleware->process($webservice, $next)->wait();

        self::assertInstanceOf(Result::class, $result1);
        self::assertInstanceOf(Result::class, $result2);

        self::assertSame([2], $result1->data());
        self::assertSame([2], $result2->data());
    }

    public function test_throws_on_corrupt_cache_entries()
    {
        $this->expectException(CorruptCacheEntryException::class);

        $webservice = new DummyCacheWebService(new stdClass(), 10);
        $pool = new ArrayCachePool();
        $middleware = new SimpleCacheMiddleware($pool);

        $pool->set($webservice->getPayloadHash(), new stdClass());

        $next = new Next(fn() => Mocks::promiseOk());

        $middleware->process($webservice, $next);
    }

    public function test_skips_corrupt_cache_entries()
    {
        $webservice = new DummyCacheWebService(new stdClass(), 10);
        $pool = new ArrayCachePool();
        $middleware = new SimpleCacheMiddleware($pool, true);

        $pool->set($webservice->getPayloadHash(), new stdClass());

        $next = new Next(fn() => Mocks::promiseOk());

        $result = $middleware->process($webservice, $next)->wait();

        self::assertInstanceOf(Result::class, $result);
    }

    public function test_marks_a_result_as_coming_from_the_cache()
    {
        $webservice = new DummyCacheWebService(new stdClass(), 10);
        $middleware = new SimpleCacheMiddleware(new ArrayCachePool());

        $result = Mocks::resultOk([1]);

        $next = new Next(function () use (&$result) {
            $result = $result->withData([$result->data()[0] + 1]);

            return new FulfilledPromise($result);
        });

        /** @var Result $result1 */
        $result1 = $middleware->process($webservice, $next)->wait();

        /** @var Result $result2 */
        $result2 = $middleware->process($webservice, $next)->wait();

        self::assertFalse($result1->isFromCache());
        self::assertTrue($result2->isFromCache());
    }
}
