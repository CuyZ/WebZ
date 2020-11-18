<?php

use CuyZ\WebZ\Core\Bus\Pipeline\Next;
use CuyZ\WebZ\Core\Bus\Pipeline\Pipeline;
use CuyZ\WebZ\Core\Bus\Pipeline\PipelineMiddleware;
use CuyZ\WebZ\Core\Result\Result;
use CuyZ\WebZ\Tests\Fixture\Middleware\IndexMiddleware;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyWebService;

it('dispatches a pipeline', function () {
    $middleware = new PipelineMiddleware(new Pipeline());

    $next = new Next(fn() => Result::mockOk(['foo' => 'bar']));

    $result = $middleware->process(new DummyWebService(new stdClass()), $next);

    expect($result)->toBeInstanceOf(Result::class);
    expect($result->data())->toBe(['foo' => 'bar']);
});

it('executes middlewares in the right order', function (array $middlewares, array $expectedOutput) {
    $pipeline = new Pipeline($middlewares);
    $middleware = new PipelineMiddleware($pipeline);
    $webService = new DummyWebService(new stdClass());

    $result = $middleware->process($webService, new Next(fn() => Result::mockOk()));

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
