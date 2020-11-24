<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Tests\Integration\Bus;

use CuyZ\WebZ\Core\Bus\Bus;
use CuyZ\WebZ\Core\Event\BeforeCallEvent;
use CuyZ\WebZ\Core\Event\FailedCallEvent;
use CuyZ\WebZ\Core\Event\SuccessfulCallEvent;
use CuyZ\WebZ\Core\Transport\Transport;
use CuyZ\WebZ\Tests\Fixture\Transport\DummyDynamicExceptionTransport;
use CuyZ\WebZ\Tests\Fixture\Transport\DummyExceptionTransport;
use CuyZ\WebZ\Tests\Fixture\Transport\DummyTransport;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyExceptionWebService;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyWebService;
use Exception;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class EventDispatchingTest extends TestCase
{
    public function synchronousEventsDataProvider(): array
    {
        return [
            [
                'transport' => new DummyTransport(),
                'eventClass' => BeforeCallEvent::class,
                'expectedFires' => 1,
            ],
            [
                'transport' => new DummyTransport(),
                'eventClass' => SuccessfulCallEvent::class,
                'expectedFires' => 1,
            ],
            [
                'transport' => new DummyTransport(),
                'eventClass' => FailedCallEvent::class,
                'expectedFires' => 0,
            ],
            [
                'transport' => new DummyExceptionTransport(),
                'eventClass' => BeforeCallEvent::class,
                'expectedFires' => 1,
            ],
            [
                'transport' => new DummyExceptionTransport(),
                'eventClass' => SuccessfulCallEvent::class,
                'expectedFires' => 0,
            ],
            [
                'transport' => new DummyExceptionTransport(),
                'eventClass' => FailedCallEvent::class,
                'expectedFires' => 1,
            ],
        ];
    }

    /**
     * @dataProvider synchronousEventsDataProvider
     * @param Transport $transport
     * @param string $eventClass
     * @param int $expectedFires
     */
    public function test_dispatches_events_synchronously(Transport $transport, string $eventClass, int $expectedFires)
    {
        $webservice = new DummyWebService(new stdClass());
        $dispatcher = new EventDispatcher();

        $fires = 0;

        $dispatcher->addListener($eventClass, function () use (&$fires) {
            $fires++;
        });

        $bus = Bus::builder()
            ->withTransport($transport)
            ->withEventDispatcher($dispatcher)
            ->build();

        try {
            $bus->call($webservice);
        } catch (\Exception $e) {
            //
        }

        self::assertSame($expectedFires, $fires);
    }

    public function asynchronousEventsDataProvider(): array
    {
        return [
            [
                'webservices' => [
                    new DummyWebService(),
                ],
                'events' => [
                    BeforeCallEvent::class => 1,
                    SuccessfulCallEvent::class => 1,
                    FailedCallEvent::class => 0,
                ],
            ],
            [
                'webservices' => [
                    new DummyWebService(),
                    new DummyWebService(),
                ],
                'events' => [
                    BeforeCallEvent::class => 2,
                    SuccessfulCallEvent::class => 2,
                    FailedCallEvent::class => 0,
                ],
            ],
            [
                'webservices' => [
                    new DummyExceptionWebService(new Exception()),
                ],
                'events' => [
                    BeforeCallEvent::class => 1,
                    SuccessfulCallEvent::class => 0,
                    FailedCallEvent::class => 1,
                ],
            ],
            [
                'webservices' => [
                    new DummyExceptionWebService(new Exception()),
                    new DummyExceptionWebService(new Exception()),
                ],
                'events' => [
                    BeforeCallEvent::class => 2,
                    SuccessfulCallEvent::class => 0,
                    FailedCallEvent::class => 2,
                ],
            ],
            [
                'webservices' => [
                    new DummyWebService(),
                    new DummyExceptionWebService(new Exception()),
                ],
                'events' => [
                    BeforeCallEvent::class => 2,
                    SuccessfulCallEvent::class => 1,
                    FailedCallEvent::class => 1,
                ],
            ],
            [
                'webservices' => [
                    new DummyExceptionWebService(new Exception()),
                    new DummyWebService(),
                ],
                'events' => [
                    BeforeCallEvent::class => 2,
                    SuccessfulCallEvent::class => 1,
                    FailedCallEvent::class => 1,
                ],
            ],
            [
                'webservices' => [
                    new DummyExceptionWebService(new Exception()),
                    new DummyWebService(),
                    new DummyExceptionWebService(new Exception()),
                ],
                'events' => [
                    BeforeCallEvent::class => 3,
                    SuccessfulCallEvent::class => 1,
                    FailedCallEvent::class => 2,
                ],
            ],
            [
                'webservices' => [
                    new DummyWebService(),
                    new DummyExceptionWebService(new Exception()),
                    new DummyWebService(),
                ],
                'events' => [
                    BeforeCallEvent::class => 3,
                    SuccessfulCallEvent::class => 2,
                    FailedCallEvent::class => 1,
                ],
            ],
        ];
    }

    /**
     * @dataProvider asynchronousEventsDataProvider
     * @param array $webservices
     * @param array $events
     */
    public function test_dispatches_events_asynchronously(array $webservices, array $events)
    {
        $dispatcher = new EventDispatcher();

        $fires = [];

        foreach ($events as $eventClass => $expectedFires) {
            $fires[$eventClass] = 0;

            $dispatcher->addListener($eventClass, function () use ($eventClass, &$fires) {
                $fires[$eventClass]++;
            });
        }

        $bus = Bus::builder()
            ->withTransport(new DummyDynamicExceptionTransport())
            ->withEventDispatcher($dispatcher)
            ->build();

        try {
            $promises = $bus->callAsync(...$webservices);

            foreach ($promises as $promise) {
                $promise->wait();
            }
        } catch (\Exception $e) {
            //
        }

        self::assertEquals($events, $fires);
    }
}
