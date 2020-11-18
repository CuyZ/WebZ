<?php

use CuyZ\WebZ\Http\Transformer\JsonTransformer;
use GuzzleHttp\Psr7\Response;
use Tests\Mocks;

it('transforms a json response to an array', function () {
    $input = ['foo' => 'bar'];

    $client = Mocks::httpClient(new Response(200, ['Content-Type' => 'application/json'], json_encode($input)));

    $response = $client->request('GET', 'http://localhost');

    $transformer = new JsonTransformer();

    $data = $transformer->toArray($response);

    expect($data)->toBe($input);
});

it('throws on invalid json', function () {
    $client = Mocks::httpClient(new Response(200, [], 'invalid json'));

    $response = $client->request('GET', 'http://localhost');

    $transformer = new JsonTransformer();

    $transformer->toArray($response);
})->throws(JsonException::class);
