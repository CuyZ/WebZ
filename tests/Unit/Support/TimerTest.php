<?php

use CuyZ\WebZ\Core\Support\Timer;

test('timer at zero', function () {
    $timer = Timer::zero();

    expect($timer->timeInSeconds())->toBe(0.0);
    expect($timer->timeInMilliseconds())->toBe(0.0);
});

test('timer', function () {
    $timer = Timer::start();

    usleep(1);

    expect($timer->timeInSeconds())->toBeGreaterThan(0);
    expect($timer->timeInMilliseconds())->toBeGreaterThan(0);
});
