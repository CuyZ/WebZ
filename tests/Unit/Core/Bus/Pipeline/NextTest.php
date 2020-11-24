<?php

namespace CuyZ\WebZ\Tests\Unit\Core\Bus\Pipeline;

use CuyZ\WebZ\Core\Bus\Pipeline\Next;
use CuyZ\WebZ\Core\Result\Result;
use CuyZ\WebZ\Core\WebService;
use CuyZ\WebZ\Tests\Fixture\Mocks;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyWebService;
use GuzzleHttp\Promise\FulfilledPromise;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \CuyZ\WebZ\Core\Bus\Pipeline\Next
 */
class NextTest extends TestCase
{
    public function test_executes_the_internal_closure()
    {
        $webService = new DummyWebService(new stdClass());

        $next = new Next(fn(WebService $webService) => new FulfilledPromise(Mocks::resultOk()));

        $result = $next($webService);

        self::assertInstanceOf(Result::class, $result->wait());
    }
}
