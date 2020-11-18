<?php

use CuyZ\WebZ\Core\Bus\Middleware;
use CuyZ\WebZ\Core\Bus\Pipeline\Next;
use CuyZ\WebZ\Core\Bus\Pipeline\Pipeline;
use CuyZ\WebZ\Core\Bus\Pipeline\StackExhaustedException;
use CuyZ\WebZ\Core\Result\Result;
use CuyZ\WebZ\Core\WebService;
use CuyZ\WebZ\Tests\Fixture\Middleware\IndexMiddleware;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyWebService;

it('throws for an empty pipeline', function () {
    $pipeline = new Pipeline();

    $pipeline->dispatch(new DummyWebService(new stdClass()));
})->throws(StackExhaustedException::class);

it('dispatches a webService', function () {
    $webService = new DummyWebService(new stdClass());

    $middleware = new class implements Middleware {
        public function process(WebService $webService, Next $next): Result
        {
            return Result::mockOk($webService->parse(['foo' => 'bar']));
        }
    };

    $pipeline = new Pipeline([$middleware]);

    $result = $pipeline->dispatch($webService);

    expect($result)->toBeInstanceOf(Result::class);
    expect($result->data())->toBe(['foo' => 'bar']);
});

it('executes middlewares in the right order', function (array $middlewares, array $expectedOutput) {
    $webService = new DummyWebService(new stdClass());
    $pipeline = new Pipeline($middlewares);

    $last = new class implements Middleware {
        public function process(WebService $webService, Next $next): Result
        {
            return Result::mockOk($webService->parse([]));
        }
    };

    $pipeline->append($last);

    $result = $pipeline->dispatch($webService);

    expect($result)->toBeInstanceOf(Result::class);
    expect($result->data())->toBe($expectedOutput);
})->with([
    [
        [
            new IndexMiddleware(0),
            new IndexMiddleware(1),
            new IndexMiddleware(2),
            new IndexMiddleware(3),
        ],
        [3, 2, 1, 0],
    ],
    [
        [
            new IndexMiddleware(3),
            new IndexMiddleware(2),
            new IndexMiddleware(1),
            new IndexMiddleware(0),
        ],
        [0, 1, 2, 3],
    ],
]);

it('appends a middleware', function () {
    $webService = new DummyWebService(new stdClass());

    $pipeline = new Pipeline([
        new IndexMiddleware(3),
        new IndexMiddleware(2),
        new IndexMiddleware(1),
        new IndexMiddleware(0),
    ]);

    $pipeline->append(new IndexMiddleware(4));

    $last = new class implements Middleware {
        public function process(WebService $webService, Next $next): Result
        {
            return Result::mockOk($webService->parse([]));
        }
    };

    $pipeline->append($last);

    $result = $pipeline->dispatch($webService);

    expect($result)->toBeInstanceOf(Result::class);
    expect($result->data())->toBe([4, 0, 1, 2, 3]);
});

it('prepends a middleware', function () {
    $webService = new DummyWebService(new stdClass());

    $pipeline = new Pipeline([
        new IndexMiddleware(3),
        new IndexMiddleware(2),
        new IndexMiddleware(1),
        new IndexMiddleware(0),
    ]);

    $pipeline->prepend(new IndexMiddleware(4));

    $last = new class implements Middleware {
        public function process(WebService $webService, Next $next): Result
        {
            return Result::mockOk($webService->parse([]));
        }
    };

    $pipeline->append($last);

    $result = $pipeline->dispatch($webService);

    expect($result)->toBeInstanceOf(Result::class);
    expect($result->data())->toBe([0, 1, 2, 3, 4]);
});
