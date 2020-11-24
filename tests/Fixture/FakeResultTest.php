<?php

namespace CuyZ\WebZ\Tests\Fixture;

use PHPUnit\Framework\TestCase;

class FakeResultTest extends TestCase
{
    public function test_wraps_an_array()
    {
        $input = ['foo' => 'bar'];

        $result = new FakeResult($input);

        self::assertSame($input, $result->raw);
    }
}
