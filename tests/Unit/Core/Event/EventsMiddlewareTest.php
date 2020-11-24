<?php

namespace CuyZ\WebZ\Tests\Unit\Core\Event;

use CuyZ\WebZ\Core\Bus\Pipeline\Next;
use CuyZ\WebZ\Core\Event\BeforeCallEvent;
use CuyZ\WebZ\Core\Event\EventsMiddleware;
use CuyZ\WebZ\Core\Event\FailedCallEvent;
use CuyZ\WebZ\Core\Event\SuccessfulCallEvent;
use CuyZ\WebZ\Tests\Fixture\Mocks;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyWebService;
use Exception;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\RejectedPromise;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * @covers \CuyZ\WebZ\Core\Event\EventsMiddleware
 */
class EventsMiddlewareTest extends TestCase
{
    public function eventsDataProvider(): array
    {
        return [
            [
                'next' => new Next(fn() => Mocks::promiseOk()),
                'events' => [
                    BeforeCallEvent::class => 1,
                    SuccessfulCallEvent::class => 1,
                    FailedCallEvent::class => 0,
                ],
            ],
            [
                'next' => new Next(function () {
                    throw new Exception();
                }),
                'events' => [
                    BeforeCallEvent::class => 1,
                    SuccessfulCallEvent::class => 0,
                    FailedCallEvent::class => 1,
                ],
            ],
            [
                'next' => new Next(fn() => new RejectedPromise(new Exception())),
                'events' => [
                    BeforeCallEvent::class => 1,
                    SuccessfulCallEvent::class => 0,
                    FailedCallEvent::class => 1,
                ],
            ],
            [
                'next' => new Next(fn() => new FulfilledPromise(Mocks::resultErr(new Exception()))),
                'events' => [
                    BeforeCallEvent::class => 1,
                    SuccessfulCallEvent::class => 0,
                    FailedCallEvent::class => 1,
                ],
            ],
        ];
    }

    /**
     * @dataProvider eventsDataProvider
     * @param Next $next
     * @param array $events
     */
    public function test_dispatches_events(Next $next, array $events)
    {
        $webservice = new DummyWebService(new stdClass());
        $dispatcher = new EventDispatcher();

        $fires = [];

        foreach ($events as $eventClass => $expectedFires) {
            $fires[$eventClass] = 0;

            $dispatcher->addListener($eventClass, function () use ($eventClass, &$fires) {
                $fires[$eventClass]++;
            });
        }

        $middleware = new EventsMiddleware($dispatcher);

        try {
            $middleware->process($webservice, $next)->wait();
        } catch (Exception $e) {
            //
        }

        self::assertEquals($events, $fires);
    }
}
