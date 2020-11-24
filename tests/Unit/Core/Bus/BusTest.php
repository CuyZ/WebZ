<?php

namespace CuyZ\WebZ\Tests\Unit\Core\Bus;

use CuyZ\WebZ\Core\Bus\Bus;
use CuyZ\WebZ\Core\Bus\Middleware;
use CuyZ\WebZ\Core\Bus\Pipeline\Next;
use CuyZ\WebZ\Core\Bus\Pipeline\Pipeline;
use CuyZ\WebZ\Core\WebService;
use CuyZ\WebZ\Tests\Fixture\Mocks;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyOutputWebService;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \CuyZ\WebZ\Core\Bus\Bus
 */
class BusTest extends TestCase
{
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
            }
        ]);

        $bus = new Bus($pipeline);

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
            }
        ]);

        $bus = new Bus($pipeline);

        $result = $bus->callAsync(new DummyOutputWebService(new stdClass(), $output));

        self::assertSame($output, $result[0]->wait());
    }
}
