<?php

namespace CuyZ\WebZ\Tests\Unit\Core\Bus;

use CuyZ\WebZ\Core\Bus\WebServiceBus;
use CuyZ\WebZ\Core\Bus\BusBuilder;
use CuyZ\WebZ\Core\Bus\Middleware;
use CuyZ\WebZ\Core\Bus\NoWebServiceException;
use CuyZ\WebZ\Core\Bus\Pipeline\Next;
use CuyZ\WebZ\Core\Bus\Pipeline\Pipeline;
use CuyZ\WebZ\Core\WebService;
use CuyZ\WebZ\Tests\Fixture\Mocks;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyOutputWebService;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyWebService;
use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \CuyZ\WebZ\Core\Bus\WebServiceBus
 */
class BusTest extends TestCase
{
    public function test_throws_when_no_webservice_given()
    {
        $this->expectException(NoWebServiceException::class);

        $pipeline = new Pipeline([
            new class implements Middleware {
                public function process(WebService $webService, Next $next): PromiseInterface
                {
                    throw new Exception();
                }
            },
        ]);

        $bus = new WebServiceBus($pipeline);

        $bus->callAsync();
    }

    public function outputDataProvider(): array
    {
        return [
            ['foo'],
            [123],
            [123.456],
            [true],
            [false],
            [null],
            [[]],
            [['foo' => 'bar']],
            [new stdClass()],
        ];
    }

    /**
     * @dataProvider outputDataProvider
     * @param mixed $output
     */
    public function test_returns_a_parsed_value_from_synchronous_bus($output)
    {
        $pipeline = new Pipeline([
            new class implements Middleware {
                public function process(WebService $webService, Next $next): PromiseInterface
                {
                    return Mocks::promiseOk();
                }
            },
        ]);

        $bus = new WebServiceBus($pipeline);

        $result = $bus->call(new DummyOutputWebService(new stdClass(), $output));

        self::assertSame($output, $result);
    }

    /**
     * @dataProvider outputDataProvider
     * @param mixed $output
     */
    public function test_returns_a_parsed_value_from_asynchronous_bus($output)
    {
        $pipeline = new Pipeline([
            new class implements Middleware {
                public function process(WebService $webService, Next $next): PromiseInterface
                {
                    return Mocks::promiseOk();
                }
            },
        ]);

        $bus = new WebServiceBus($pipeline);

        $result = $bus->callAsync(new DummyOutputWebService(new stdClass(), $output));

        self::assertSame($output, $result[0]->wait());
    }

    public function test_creates_a_builder()
    {
        self::assertInstanceOf(BusBuilder::class, WebServiceBus::builder());
    }

    public function test_webservices_have_same_async_call_hash_when_async_call()
    {
        $middleware = new class implements Middleware {
            public array $hashes = [];

            public function process(WebService $webService, Next $next): PromiseInterface
            {
                $this->hashes[] = $webService->getAsyncCallHash();

                return Mocks::promiseOk();
            }
        };

        $pipeline = new Pipeline([$middleware]);

        $bus = new WebServiceBus($pipeline);

        $bus->callAsync(
            new DummyWebService(),
            new DummyWebService(),
        );

        self::assertCount(1, array_unique($middleware->hashes));
    }
}
