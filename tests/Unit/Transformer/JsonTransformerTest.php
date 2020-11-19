<?php

use CuyZ\WebZ\Http\Transformer\JsonTransformer;
use GuzzleHttp\Psr7\Response;

it('transforms a json response to an array', function () {
    $input = ['foo' => 'bar'];

    $response = new Response(200, ['Content-Type' => 'application/json'], json_encode($input));

    $transformer = new JsonTransformer();

    $data = $transformer->toArray($response);

    expect($data)->toBe($input);
});

it('throws on invalid json', function () {
    $response = new Response(200, [], 'invalid json');

    $transformer = new JsonTransformer();

    $transformer->toArray($response);
})->throws(JsonException::class);
