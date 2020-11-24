<?php

namespace CuyZ\WebZ\Tests\Unit\Core\Result;

use CuyZ\WebZ\Core\Result\RawResult;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CuyZ\WebZ\Core\Result\RawResult
 */
class RawResultTest extends TestCase
{
    public function test_creates_a_successful_result()
    {
        $result = RawResult::ok();

        self::assertIsArray($result->data());
        self::assertEmpty($result->data());
        self::assertNull($result->exception());
        self::assertNull($result->requestTrace());
        self::assertNull($result->responseTrace());
    }

    public function test_creates_a_failed_result()
    {
        $e = new Exception();

        $result = RawResult::err($e);

        self::assertIsArray($result->data());
        self::assertEmpty($result->data());
        self::assertSame($e, $result->exception());
        self::assertNull($result->requestTrace());
        self::assertNull($result->responseTrace());
    }

    public function rawDataProvider(): array
    {
        return [
            [
                'raw' => RawResult::ok(['foo' => 'bar']),
                'expected' => ['foo' => 'bar'],
            ],
            [
                'raw' => RawResult::err(new Exception(), ['bar' => 'foo']),
                'expected' => ['bar' => 'foo'],
            ],
        ];
    }

    /**
     * @dataProvider rawDataProvider
     * @param RawResult $result
     * @param array $expectedData
     */
    public function test_can_hold_data(RawResult $result, array $expectedData)
    {
        self::assertSame($expectedData, $result->data());
    }

    public function test_can_hold_the_request_trace()
    {
        $result = RawResult::ok();

        $newResult = $result->withRequestTrace('request');

        self::assertNull($result->requestTrace());
        self::assertSame('request', $newResult->requestTrace());
    }

    public function test_can_hold_the_response_trace()
    {
        $result = RawResult::ok();

        $newResult = $result->withResponseTrace('response');

        self::assertNull($result->responseTrace());
        self::assertSame('response', $newResult->responseTrace());
    }
}
