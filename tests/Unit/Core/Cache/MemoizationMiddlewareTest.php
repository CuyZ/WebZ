<?php

namespace CuyZ\WebZ\Tests\Unit\Core\Cache;

use CuyZ\WebZ\Core\Bus\Pipeline\Next;
use CuyZ\WebZ\Core\Cache\MemoizationMiddleware;
use CuyZ\WebZ\Core\Result\Result;
use CuyZ\WebZ\Tests\Fixture\Mocks;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyCacheWebService;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyWebService;
use GuzzleHttp\Promise\FulfilledPromise;
use PHPUnit\Framework\TestCase;
use stdClass;

class MemoizationMiddlewareTest extends TestCase
{
    public function test_ignores_incompatible_webservices()
    {
        $webservice = new DummyWebService(new stdClass());
        $middleware = new MemoizationMiddleware();

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

    public function test_memoize_results_if_cache_TTL_is_greater_than_0()
    {
        $webservice = new DummyCacheWebService(new stdClass(), 10);
        $middleware = new MemoizationMiddleware();

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

    public function test_does_not_memoize_results_if_cache_TTL_is_lower_or_equal_to_0()
    {
        $webservice = new DummyCacheWebService(new stdClass(), 0);
        $middleware = new MemoizationMiddleware();

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
}
