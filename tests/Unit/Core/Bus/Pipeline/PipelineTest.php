<?php

namespace CuyZ\WebZ\Tests\Unit\Core\Bus\Pipeline;

use CuyZ\WebZ\Core\Bus\Middleware;
use CuyZ\WebZ\Core\Bus\Pipeline\Next;
use CuyZ\WebZ\Core\Bus\Pipeline\Pipeline;
use CuyZ\WebZ\Core\Bus\Pipeline\StackExhaustedException;
use CuyZ\WebZ\Core\Result\Result;
use CuyZ\WebZ\Core\WebService;
use CuyZ\WebZ\Tests\Fixture\Middleware\IndexMiddleware;
use CuyZ\WebZ\Tests\Fixture\Mocks;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyWebService;
use GuzzleHttp\Promise\PromiseInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \CuyZ\WebZ\Core\Bus\Pipeline\Pipeline
 */
class PipelineTest extends TestCase
{
    public function test_throws_for_an_empty_pipeline()
    {
        $this->expectException(StackExhaustedException::class);

        $pipeline = new Pipeline();

        $pipeline->dispatch(new DummyWebService(new stdClass()));
    }

    public function test_dispatch_a_webService()
    {
        $webService = new DummyWebService(new stdClass());

        $middleware = new class implements Middleware {
            public function process(WebService $webService, Next $next): PromiseInterface
            {
                return Mocks::promiseOk($webService->parse(['foo' => 'bar']));
            }
        };

        $pipeline = new Pipeline([$middleware]);

        /** @var Result $result */
        $result = $pipeline->dispatch($webService)->wait();

        self::assertInstanceOf(Result::class, $result);
        self::assertSame(['foo' => 'bar'], $result->data());
    }

    public function orderingDataProvider(): array
    {
        return [
            [
                'middlewares' => [
                    new IndexMiddleware(0),
                    new IndexMiddleware(1),
                    new IndexMiddleware(2),
                    new IndexMiddleware(3),
                ],
                'output' => [3, 2, 1, 0],
            ],
            [
                'middlewares' => [
                    new IndexMiddleware(3),
                    new IndexMiddleware(2),
                    new IndexMiddleware(1),
                    new IndexMiddleware(0),
                ],
                'output' => [0, 1, 2, 3],
            ],
        ];
    }

    /**
     * @dataProvider orderingDataProvider
     * @param array $middlewares
     * @param array $expectedOutput
     */
    public function test_execute_middlewares_in_the_right_order(array $middlewares, array $expectedOutput)
    {
        $webService = new DummyWebService(new stdClass());
        $pipeline = new Pipeline($middlewares);

        $last = new class implements Middleware {
            public function process(WebService $webService, Next $next): PromiseInterface
            {
                return Mocks::promiseOk($webService->parse([]));
            }
        };

        $pipeline->append($last);

        /** @var Result $result */
        $result = $pipeline->dispatch($webService)->wait();

        self::assertSame($expectedOutput, $result->data());
    }

    public function test_append_a_middleware()
    {
        $webService = new DummyWebService(new stdClass());

        $pipeline = new Pipeline([
            new IndexMiddleware(3),
            new IndexMiddleware(2),
            new IndexMiddleware(1),
            new IndexMiddleware(0),
        ]);

        $pipeline->append(new IndexMiddleware(4));

        $last = new class implements Middleware {
            public function process(WebService $webService, Next $next): PromiseInterface
            {
                return Mocks::promiseOk($webService->parse([]));
            }
        };

        $pipeline->append($last);

        /** @var Result $result */
        $result = $pipeline->dispatch($webService)->wait();

        self::assertSame([4, 0, 1, 2, 3], $result->data());
    }

    public function test_prepend_a_middleware()
    {
        $webService = new DummyWebService(new stdClass());

        $pipeline = new Pipeline([
            new IndexMiddleware(3),
            new IndexMiddleware(2),
            new IndexMiddleware(1),
            new IndexMiddleware(0),
        ]);

        $pipeline->prepend(new IndexMiddleware(4));

        $last = new class implements Middleware {
            public function process(WebService $webService, Next $next): PromiseInterface
            {
                return Mocks::promiseOk($webService->parse([]));
            }
        };

        $pipeline->append($last);

        /** @var Result $result */
        $result = $pipeline->dispatch($webService)->wait();

        self::assertSame([0, 1, 2, 3, 4], $result->data());
    }
}
