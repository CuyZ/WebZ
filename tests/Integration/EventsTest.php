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

it('dispatches events', function (Transport $transport, string $eventClass, bool $shouldFire) {
    $webservice = new DummyWebService(new stdClass());
    $dispatcher = new EventDispatcher();

    $hasFired = false;

    $dispatcher->addListener($eventClass, function () use (&$hasFired) {
        $hasFired = true;
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

    expect($hasFired)->toBe($shouldFire);
})->with([
    [
        'transport' => new DummyTransport(),
        'eventClass' => BeforeCallEvent::class,
        'shouldFire' => true,
    ],
    [
        'transport' => new DummyTransport(),
        'eventClass' => SuccessfulCallEvent::class,
        'shouldFire' => true,
    ],
    [
        'transport' => new DummyTransport(),
        'eventClass' => FailedCallEvent::class,
        'shouldFire' => false,
    ],
    [
        'transport' => new DummyExceptionTransport(),
        'eventClass' => BeforeCallEvent::class,
        'shouldFire' => true,
    ],
    [
        'transport' => new DummyExceptionTransport(),
        'eventClass' => SuccessfulCallEvent::class,
        'shouldFire' => false,
    ],
    [
        'transport' => new DummyExceptionTransport(),
        'eventClass' => FailedCallEvent::class,
        'shouldFire' => true,
    ],
]);
