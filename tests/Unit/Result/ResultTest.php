<?php

use CuyZ\WebZ\Core\Result\RawResult;
use CuyZ\WebZ\Core\Result\Result;
use CuyZ\WebZ\Core\Support\Timer;

it('creates a successful result', function () {
    $timer = Timer::zero();

    $raw = RawResult::ok(['foo' => 'bar'])
        ->withRequestTrace('request')
        ->withResponseTrace('response');

    $result = new Result($raw, $timer);

    expect($result->data())->toBe(['foo' => 'bar']);
    expect($result->exception())->toBeNull();
    expect($result->timer())->toBe($timer);
    expect($result->requestTrace())->toBe('request');
    expect($result->responseTrace())->toBe('response');
});

it('creates a failed result', function () {
    $exception = new Exception();
    $timer = Timer::zero();

    $raw = RawResult::err($exception, ['foo' => 'bar'])
        ->withRequestTrace('request')
        ->withResponseTrace('response');

    $result = new Result($raw, $timer);

    expect($result->data())->toBe(['foo' => 'bar']);
    expect($result->exception())->toBe($exception);
    expect($result->timer())->toBe($timer);
    expect($result->requestTrace())->toBe('request');
    expect($result->responseTrace())->toBe('response');
});

it('sets the data', function () {
    $raw = RawResult::ok(['foo' => 'bar']);

    $result = new Result($raw, Timer::zero());

    $newResult = $result->withData(['abc' => 'def']);

    expect($result->data())->toBe(['foo' => 'bar']);
    expect($newResult->data())->toBe(['abc' => 'def']);
});

it('is marked as not coming from cache by default', function () {
    $result = new Result(RawResult::ok(), Timer::zero());

    expect($result->isFromCache())->toBeFalse();
});

it('sets as coming from cache', function () {
    $result = new Result(RawResult::ok(), Timer::zero());

    $newResult = $result->markAsComingFromCache();

    expect($result->isFromCache())->toBeFalse();
    expect($newResult->isFromCache())->toBeTrue();
});
