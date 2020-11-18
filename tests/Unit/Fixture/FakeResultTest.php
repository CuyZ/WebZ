<?php

use CuyZ\WebZ\Tests\Fixture\FakeResult;

it('wraps an array', function () {
    $input = ['foo' => 'bar'];

    $result = new FakeResult($input);

    expect($result->raw)->toBe($input);
});
