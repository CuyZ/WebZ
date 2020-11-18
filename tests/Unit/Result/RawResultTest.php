<?php

use CuyZ\WebZ\Core\Result\RawResult;

it('creates a successful result', function () {
    $result = RawResult::ok();

    expect($result->data())->toBeArray()->toBeEmpty();
    expect($result->exception())->toBeNull();
});

it('creates a failed result', function () {
    $e = new Exception();

    $result = RawResult::err($e);

    expect($result->data())->toBeArray()->toBeEmpty();
    expect($result->exception())->toBe($e);
});

it('can hold data', function (RawResult $result, array $expectedData) {
    expect($result->data())->toBe($expectedData);
})->with([
    [RawResult::ok(['foo' => 'bar']), ['foo' => 'bar']],
    [RawResult::err(new Exception(), ['bar' => 'foo']), ['bar' => 'foo']],
]);

it('can hold the request trace', function () {
    $result = RawResult::ok();

    $newResult = $result->withRequestTrace('request');

    expect($result->requestTrace())->toBeNull();
    expect($newResult->requestTrace())->toBe('request');
});

it('can hold the response trace', function () {
    $result = RawResult::ok();

    $newResult = $result->withResponseTrace('response');

    expect($result->responseTrace())->toBeNull();
    expect($newResult->responseTrace())->toBe('response');
});
