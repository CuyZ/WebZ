<?php

use CuyZ\WebZ\Core\Support\Arr;

it('throws on invalid input', function () {
    Arr::castToArray('foo');
})->throws(InvalidArgumentException::class);

it('casts null to an empty array', function () {
    $result = Arr::castToArray(null);

    expect($result)->toBeArray();
    expect($result)->toBeEmpty();
});

it('casts an object to an array', function () {
    $input = new stdClass();
    $input->a = new stdClass();
    $input->a->b = new stdClass();
    $input->a->b->c = 'foo';

    expect(Arr::castToArray($input))->toBe([
        'a' => [
            'b' => [
                'c' => 'foo',
            ],
        ],
    ]);
});

it('casts an array to an array', function () {
    $input = [
        'a' => [
            'b' => [
                'c' => 'foo',
            ],
        ],
    ];

    expect(Arr::castToArray($input))->toBe([
        'a' => [
            'b' => [
                'c' => 'foo',
            ],
        ],
    ]);
});

it('casts a mix of arrays and objects to an array', function () {
    $input = new stdClass();
    $input->a = [];
    $input->a['b'] = new stdClass();
    $input->a['b']->c = 'foo';

    expect(Arr::castToArray($input))->toBe([
        'a' => [
            'b' => [
                'c' => 'foo',
            ],
        ],
    ]);
});
