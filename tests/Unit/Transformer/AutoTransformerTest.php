<?php

use CuyZ\WebZ\Http\Transformer\AutoTransformer;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

it('transforms a json response to an array', function () {
    $input = ['foo' => 'bar'];

    $client = new MockHttpClient(new MockResponse(json_encode($input)));
    $response = $client->request('GET', 'http://localhost');

    $transformer = new AutoTransformer();

    $data = $transformer->toArray($response);

    expect($data)->toBe($input);
});
