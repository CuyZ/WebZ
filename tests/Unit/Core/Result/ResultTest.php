<?php

namespace CuyZ\WebZ\Tests\Unit\Core\Result;

use CuyZ\WebZ\Core\Result\RawResult;
use CuyZ\WebZ\Core\Result\Result;
use CuyZ\WebZ\Tests\Fixture\Mocks;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CuyZ\WebZ\Core\Result\Result
 */
class ResultTest extends TestCase
{
    public function test_creates_a_successful_result()
    {
        $timer = Mocks::timer();

        $raw = RawResult::ok(['foo' => 'bar'])
            ->withRequestTrace('request')
            ->withResponseTrace('response');

        $result = new Result($raw, $timer);

        self::assertSame(['foo' => 'bar'], $result->data());
        self::assertNull($result->exception());
        self::assertSame($timer, $result->timer());
        self::assertSame('request', $result->requestTrace());
        self::assertSame('response', $result->responseTrace());
    }

    public function test_creates_a_failed_result()
    {
        $exception = new Exception();
        $timer = Mocks::timer();

        $raw = RawResult::err($exception, ['foo' => 'bar'])
            ->withRequestTrace('request')
            ->withResponseTrace('response');

        $result = new Result($raw, $timer);

        self::assertSame(['foo' => 'bar'], $result->data());
        self::assertSame($exception, $result->exception());
        self::assertSame($timer, $result->timer());
        self::assertSame('request', $result->requestTrace());
        self::assertSame('response', $result->responseTrace());
    }

    public function test_sets_the_data()
    {
        $raw = RawResult::ok(['foo' => 'bar']);

        $result = new Result($raw, Mocks::timer());

        $newResult = $result->withData(['abc' => 'def']);

        self::assertSame(['foo' => 'bar'], $result->data());
        self::assertSame(['abc' => 'def'], $newResult->data());
    }

    public function test_is_marked_as_not_coming_from_cache_by_default()
    {
        $result = new Result(RawResult::ok(), Mocks::timer());

        self::assertFalse($result->isFromCache());
    }

    public function test_sets_as_coming_from_cache()
    {
        $result = new Result(RawResult::ok(), Mocks::timer());

        $newResult = $result->markAsComingFromCache();

        self::assertFalse($result->isFromCache());
        self::assertTrue($newResult->isFromCache());
    }
}
