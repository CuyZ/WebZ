<?php

use CuyZ\WebZ\Core\Bus\Pipeline\Next;
use CuyZ\WebZ\Core\Result\Result;
use CuyZ\WebZ\Core\WebService;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyWebService;
use GuzzleHttp\Promise\FulfilledPromise;
use Tests\Mocks;

it('executes the internal closure', function () {
    $webService = new DummyWebService(new stdClass());

    $next = new Next(fn(WebService $webService) => new FulfilledPromise(Mocks::resultOk()));

    $result = $next($webService);

    expect($result->wait())->toBeInstanceOf(Result::class);
});
