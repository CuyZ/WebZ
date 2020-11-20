<?php

use CuyZ\WebZ\Core\Bus\Bus;
use CuyZ\WebZ\Core\Event\BeforeCallEvent;
use CuyZ\WebZ\Core\Event\FailedCallEvent;
use CuyZ\WebZ\Core\Event\SuccessfulCallEvent;
use CuyZ\WebZ\Core\Transport\Transport;
use CuyZ\WebZ\Tests\Fixture\Transport\DummyExceptionTransport;
use CuyZ\WebZ\Tests\Fixture\Transport\DummyTransport;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyWebService;
use Symfony\Component\EventDispatcher\EventDispatcher;

it('dispatches events', function (Transport $transport, string $eventClass, int $expectedFires) {
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

    expect($fires)->toBe($expectedFires);
})->with([
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
]);
