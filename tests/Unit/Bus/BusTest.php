<?php

use CuyZ\WebZ\Core\Bus\Bus;
use CuyZ\WebZ\Core\Bus\Middleware;
use CuyZ\WebZ\Core\Bus\Pipeline\Next;
use CuyZ\WebZ\Core\Bus\Pipeline\Pipeline;
use CuyZ\WebZ\Core\Result\Result;
use CuyZ\WebZ\Core\WebService;
use CuyZ\WebZ\Tests\Fixture\Transport\DummyTransport;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyOutputWebService;

dataset('data', [
    ['foo'],
    [123],
    [123.456],
    [true],
    [false],
    [null],
    [[]],
    [['foo' => 'bar']],
    [new stdClass()],
]);

it('returns a parsed value from pipeline', function ($output) {
    $pipeline = new Pipeline([
        new class implements Middleware
        {
            public function process(WebService $webService, Next $next): Result
            {
                return Result::mockOk();
            }
        }
    ]);

    $bus = new Bus($pipeline);

    $result = $bus->call(new DummyOutputWebService(new stdClass(), $output));

    expect($result)->toBe($output);
})->with('data');

it('returns a parsed value from builder', function ($output) {
    $bus = Bus::builder()
        ->withTransport(new DummyTransport())
        ->build();

    $result = $bus->call(new DummyOutputWebService(new stdClass(), $output));

    expect($result)->toBe($output);
})->with('data');
