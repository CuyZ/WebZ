<?php

use CuyZ\WebZ\Http\Transformer\ScalarTransformer;
use GuzzleHttp\Psr7\Response;

it('transforms a scalar value to an array', function ($input, string $expected) {
    $response = new Response(200, [], $input);

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
    [json_encode(['foo' => 'bar']), '{"foo":"bar"}'],
    ['<div>Hello</div>', '<div>Hello</div>'],
]);
