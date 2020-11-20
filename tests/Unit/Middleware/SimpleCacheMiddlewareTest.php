<?php

use Cache\Adapter\PHPArray\ArrayCachePool;
use CuyZ\WebZ\Core\Bus\Pipeline\Next;
use CuyZ\WebZ\Core\Cache\CorruptCacheEntryException;
use CuyZ\WebZ\Core\Cache\SimpleCacheMiddleware;
use CuyZ\WebZ\Core\Result\Result;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyCacheWebService;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyWebService;
use GuzzleHttp\Promise\FulfilledPromise;
use Tests\Mocks;

it('ignores incompatible webservices', function () {
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

    expect($result1)->toBeInstanceOf(Result::class);
    expect($result2)->toBeInstanceOf(Result::class);

    expect($result1->data())->toBe([2]);
    expect($result2->data())->toBe([3]);
});

it('does not store in cache if ttl is 0', function () {
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

    expect($result1)->toBeInstanceOf(Result::class);
    expect($result2)->toBeInstanceOf(Result::class);

    expect($result1->data())->toBe([2]);
    expect($result2->data())->toBe([3]);
});

it('stores the result in cache', function () {
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

    expect($result1)->toBeInstanceOf(Result::class);
    expect($result2)->toBeInstanceOf(Result::class);

    expect($result1->data())->toBe([2]);
    expect($result2->data())->toBe([2]);
});

it('throws on corrupt cache entries', function () {
    $webservice = new DummyCacheWebService(new stdClass(), 10);
    $pool = new ArrayCachePool();
    $middleware = new SimpleCacheMiddleware($pool);

    $pool->set($webservice->getPayloadHash(), new stdClass());

    $next = new Next(fn() => Mocks::promiseOk());

    $middleware->process($webservice, $next);
})->throws(CorruptCacheEntryException::class);

it('skips corrupt cache entries', function () {
    $webservice = new DummyCacheWebService(new stdClass(), 10);
    $pool = new ArrayCachePool();
    $middleware = new SimpleCacheMiddleware($pool, true);

    $pool->set($webservice->getPayloadHash(), new stdClass());

    $next = new Next(fn() => Mocks::promiseOk());

    $result = $middleware->process($webservice, $next)->wait();

    expect($result)->toBeInstanceOf(Result::class);
});

it('marks a result as coming from the cache', function () {
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

    expect($result1)->toBeInstanceOf(Result::class);
    expect($result2)->toBeInstanceOf(Result::class);

    expect($result1->data())->toBe([2]);
    expect($result2->data())->toBe([2]);

    expect($result1->isFromCache())->toBeFalse();
    expect($result2->isFromCache())->toBeTrue();
});
