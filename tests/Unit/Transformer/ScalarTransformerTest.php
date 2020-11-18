<?php

use CuyZ\WebZ\Http\Transformer\ScalarTransformer;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

it('transforms a scalar value to an array', function ($input, string $expected) {
    $client = new MockHttpClient(new MockResponse($input));
    $response = $client->request('GET', 'http://localhost');

    $transformer = new ScalarTransformer();

    $data = $transformer->toArray($response);

    expect($data)->toBe(['value' => $expected]);
})->with([
    ['foo', 'foo'],
    [123, '123'],
    [12.34, '12.34'],
    [true, '1'],
    [false, ''],
    [null, ''],
    [[], ''],
]);
