<?php

use CuyZ\WebZ\Core\Bus\Pipeline\Next;
use CuyZ\WebZ\Core\Cache\MemoizationMiddleware;
use CuyZ\WebZ\Core\Result\Result;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyCacheWebService;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyWebService;

it('ignores incompatible webservices', function () {
    $webservice = new DummyWebService(new stdClass());
    $middleware = new MemoizationMiddleware();

    $result = Result::mockOk([1]);

    $next = new Next(function () use (&$result) {
        $result = $result->withData([$result->data()[0] + 1]);

        return $result;
    });

    $result1 = $middleware->process($webservice, $next);
    $result2 = $middleware->process($webservice, $next);

    expect($result1->data())->toBe([2]);
    expect($result2->data())->toBe([3]);
});

it('memoize results if cache TTL is greater than 0', function () {
    $webservice = new DummyCacheWebService(new stdClass(), 10);
    $middleware = new MemoizationMiddleware();

    $result = Result::mockOk([1]);

    $next = new Next(function () use (&$result) {
        $result = $result->withData([$result->data()[0] + 1]);

        return $result;
    });

    $result1 = $middleware->process($webservice, $next);
    $result2 = $middleware->process($webservice, $next);

    expect($result1->data())->toBe([2]);
    expect($result2->data())->toBe([2]);
});

it('does not memoize results if cache TTL is lower or equal to 0', function () {
    $webservice = new DummyCacheWebService(new stdClass(), 0);
    $middleware = new MemoizationMiddleware();

    $result = Result::mockOk([1]);

    $next = new Next(function () use (&$result) {
        $result = $result->withData([$result->data()[0] + 1]);

        return $result;
    });

    $result1 = $middleware->process($webservice, $next);
    $result2 = $middleware->process($webservice, $next);

    expect($result1->data())->toBe([2]);
    expect($result2->data())->toBe([3]);
});
