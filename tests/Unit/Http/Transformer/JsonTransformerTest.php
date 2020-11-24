<?php

namespace CuyZ\WebZ\Tests\Unit\Http\Transformer;

use CuyZ\WebZ\Http\Transformer\JsonTransformer;
use GuzzleHttp\Psr7\Response;
use JsonException;
use PHPUnit\Framework\TestCase;

class JsonTransformerTest extends TestCase
{
    public function test_transforms_a_json_response_to_an_array()
    {
        $input = ['foo' => 'bar'];

        $response = new Response(200, ['Content-Type' => 'application/json'], json_encode($input));

        $transformer = new JsonTransformer();

        $data = $transformer->toArray($response);

        self::assertSame($input, $data);
    }

    public function test_throws_on_invalid_json()
    {
        $this->expectException(JsonException::class);

        $response = new Response(200, [], 'invalid_json');

        $transformer = new JsonTransformer();

        $transformer->toArray($response);
    }
}
