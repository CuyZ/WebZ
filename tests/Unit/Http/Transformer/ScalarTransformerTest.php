<?php

namespace CuyZ\WebZ\Tests\Unit\Http\Transformer;

use CuyZ\WebZ\Http\Transformer\ScalarTransformer;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CuyZ\WebZ\Http\Transformer\ScalarTransformer
 */
class ScalarTransformerTest extends TestCase
{
    public function valuesDataProvider(): array
    {
        return [
            ['foo', 'foo'],
            [123, '123'],
            [12.34, '12.34'],
            [true, '1'],
            [false, ''],
            [null, ''],
            [json_encode(['foo' => 'bar']), '{"foo":"bar"}'],
            ['<div>Hello</div>', '<div>Hello</div>'],
        ];
    }

    /**
     * @dataProvider valuesDataProvider
     * @param mixed $input
     * @param string $expected
     */
    public function test_transforms_a_scalar_value_to_an_array($input, string $expected)
    {
        $response = new Response(200, [], $input);

        $transformer = new ScalarTransformer();

        $data = $transformer->toArray($response);

        self::assertSame(['value' => $expected], $data);
    }
}
