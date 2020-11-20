<?php

use CuyZ\WebZ\Core\Bus\Pipeline\Next;
use CuyZ\WebZ\Core\Event\BeforeCallEvent;
use CuyZ\WebZ\Core\Event\EventsMiddleware;
use CuyZ\WebZ\Core\Event\FailedCallEvent;
use CuyZ\WebZ\Core\Event\SuccessfulCallEvent;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyWebService;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Tests\Mocks;

it('dispatches events', function (Next $next, string $eventClass, int $expectedFires) {
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

    expect($fires)->toBe($expectedFires);
})->with([
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
]);
