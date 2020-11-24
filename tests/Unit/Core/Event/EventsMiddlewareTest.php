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
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\EventDispatcher\EventDispatcher;

class EventsMiddlewareTest extends TestCase
{
    public function eventsDataProvider(): array
    {
        return [
            [
                'next' => new Next(fn() => Mocks::promiseOk()),
                'event' => BeforeCallEvent::class,
                'fires' => 1,
            ],
            [
                'next' => new Next(fn() => Mocks::promiseOk()),
                'event' => FailedCallEvent::class,
                'fires' => 0,
            ],
            [
                'next' => new Next(fn() => Mocks::promiseOk()),
                'event' => SuccessfulCallEvent::class,
                'fires' => 1,
            ],

            [
                'next' => new Next(function () {
                    throw new Exception();
                }),
                'event' => BeforeCallEvent::class,
                'fires' => 1,
            ],
            [
                'next' => new Next(function () {
                    throw new Exception();
                }),
                'event' => FailedCallEvent::class,
                'fires' => 1,
            ],
            [
                'next' => new Next(function () {
                    throw new Exception();
                }),
                'event' => SuccessfulCallEvent::class,
                'fires' => 0,
            ],
        ];
    }

    /**
     * @dataProvider eventsDataProvider
     * @param Next $next
     * @param string $eventClass
     * @param int $expectedFires
     */
    public function test_dispatches_events(Next $next, string $eventClass, int $expectedFires)
    {
        $webservice = new DummyWebService(new stdClass());
        $dispatcher = new EventDispatcher();

        $fires = 0;

        $dispatcher->addListener($eventClass, function () use (&$fires) {
            $fires++;
        });

        $middleware = new EventsMiddleware($dispatcher);

        try {
            $middleware->process($webservice, $next)->wait();
        } catch (Exception $e) {
            //
        }

        self::assertSame($expectedFires, $fires);
    }
}
