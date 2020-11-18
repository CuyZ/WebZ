<?php

use CuyZ\WebZ\Core\Event\BeforeCallEvent;
use CuyZ\WebZ\Core\Event\FailedCallEvent;
use CuyZ\WebZ\Core\Event\SuccessfulCallEvent;
use CuyZ\WebZ\Core\Result\Result;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyWebService;

it('creates a BeforeCallEvent', function () {
    $webService = new DummyWebService(new stdClass());

    $event = new BeforeCallEvent($webService);

    expect($event->webService)->toBe($webService);
});

it('creates a SuccessfulCallEvent', function () {
    $webService = new DummyWebService(new stdClass());
    $result = Result::mockOk();

    $event = new SuccessfulCallEvent($webService, $result);

    expect($event->webService)->toBe($webService);
    expect($event->result)->toBe($result);
});

it('creates a FailedCallEvent', function () {
    $webService = new DummyWebService(new stdClass());
    $exception = new Exception();

    $event = new FailedCallEvent($webService, $exception);

    expect($event->webService)->toBe($webService);
    expect($event->exception)->toBe($exception);
});
