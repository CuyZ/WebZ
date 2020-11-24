<?php

namespace CuyZ\WebZ\Tests\Unit\Core\Support;

use CuyZ\WebZ\Core\Support\Arr;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \CuyZ\WebZ\Core\Support\Arr
 */
class ArrTest extends TestCase
{
    public function test_throws_on_invalid_input()
    {
        $this->expectException(InvalidArgumentException::class);

        Arr::castToArray('foo');
    }

    public function valuesDataProvider()
    {
        yield [
            'input' => null,
            'output' => [],
        ];

        $objectsOnly = new stdClass();
        $objectsOnly->a = new stdClass();
        $objectsOnly->a->b = new stdClass();
        $objectsOnly->a->b->c = 'foo';

        yield [
            'input' => $objectsOnly,
            'output' => [
                'a' => [
                    'b' => [
                        'c' => 'foo',
                    ],
                ],
            ],
        ];

        $arrayOnly = [
            'a' => [
                'b' => [
                    'c' => 'foo',
                ],
            ],
        ];

        yield [
            'input' => $arrayOnly,
            'output' => $arrayOnly,
        ];

        $mix = new stdClass();
        $mix->a = [];
        $mix->a['b'] = new stdClass();
        $mix->a['b']->c = 'foo';

        yield [
            'input' => $mix,
            'output' => [
                'a' => [
                    'b' => [
                        'c' => 'foo',
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider valuesDataProvider
     * @param mixed $input
     * @param array $expectedOutput
     */
    public function test_casts_input_to_an_array($input, array $expectedOutput)
    {
        $output = Arr::castToArray($input);

        self::assertSame($expectedOutput, $output);
    }
}
