<?php

namespace CuyZ\WebZ\Tests\Unit\Core\Bus\Pipeline;

use CuyZ\WebZ\Core\Bus\Pipeline\Next;
use CuyZ\WebZ\Core\Bus\Pipeline\Pipeline;
use CuyZ\WebZ\Core\Bus\Pipeline\PipelineMiddleware;
use CuyZ\WebZ\Core\Result\Result;
use CuyZ\WebZ\Tests\Fixture\Middleware\IndexMiddleware;
use CuyZ\WebZ\Tests\Fixture\Mocks;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyWebService;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \CuyZ\WebZ\Core\Bus\Pipeline\PipelineMiddleware
 */
class PipelineMiddlewareTest extends TestCase
{
    public function test_dispatch_a_pipeline()
    {
        $middleware = new PipelineMiddleware(new Pipeline());

        $next = new Next(fn() => Mocks::promiseOk(["foo" => "bar"]));

        /** @var Result $result */
        $result = $middleware->process(new DummyWebService(new stdClass()), $next)->wait();

        self::assertSame(['foo' => 'bar'], $result->data());
    }

    public function middlewaresDataProvider(): array
    {
        return [
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
        ];
    }

    /**
     * @dataProvider middlewaresDataProvider
     * @param array $middlewares
     * @param array $expectedOutput
     */
    public function test_execute_internal_middlewares_in_the_right_order(array $middlewares, array $expectedOutput)
    {
        $pipeline = new Pipeline($middlewares);
        $middleware = new PipelineMiddleware($pipeline);
        $webService = new DummyWebService(new stdClass());

        /** @var Result $result */
        $result = $middleware->process($webService, new Next(fn() => Mocks::promiseOk()))->wait();

        self::assertSame($expectedOutput, $result->data());
    }
}
