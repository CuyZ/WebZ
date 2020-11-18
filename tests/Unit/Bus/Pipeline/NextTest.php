<?php

use CuyZ\WebZ\Core\Bus\Pipeline\Next;
use CuyZ\WebZ\Core\Result\Result;
use CuyZ\WebZ\Core\WebService;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyWebService;

it('executes the internal closure', function () {
    $webService = new DummyWebService(new stdClass());

    $next = new Next(fn (WebService $webService) => Result::mockOk());

    $result = $next($webService);

    expect($result)->toBeInstanceOf(Result::class);
});
