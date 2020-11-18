<?php

use CuyZ\WebZ\Core\Bus\Pipeline\Next;
use CuyZ\WebZ\Core\Event\BeforeCallEvent;
use CuyZ\WebZ\Core\Event\EventsMiddleware;
use CuyZ\WebZ\Core\Event\FailedCallEvent;
use CuyZ\WebZ\Core\Event\SuccessfulCallEvent;
use CuyZ\WebZ\Core\Result\Result;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyWebService;
use Symfony\Component\EventDispatcher\EventDispatcher;

it('dispatches events', function (Next $next, string $eventClass, bool $shouldFire) {
    $webservice = new DummyWebService(new stdClass());
    $dispatcher = new EventDispatcher();

    $hasFired = false;

    $dispatcher->addListener($eventClass, function () use (&$hasFired) {
        $hasFired = true;
    });

    $middleware = new EventsMiddleware($dispatcher);

    try {
        $middleware->process($webservice, $next);
    } catch (\Exception $e) {
        //
    }

    expect($hasFired)->toBe($shouldFire);
})->with([
    [new Next(fn () => Result::mockOk()), BeforeCallEvent::class, true],
    [new Next(fn () => Result::mockOk()), FailedCallEvent::class, false],
    [new Next(fn () => Result::mockOk()), SuccessfulCallEvent::class, true],

    [new Next(function () { throw new Exception(); }), BeforeCallEvent::class, true],
    [new Next(function () { throw new Exception(); }), FailedCallEvent::class, true],
    [new Next(function () { throw new Exception(); }), SuccessfulCallEvent::class, false],
]);
