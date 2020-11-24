<?php

namespace CuyZ\WebZ\Tests\Unit\Core\Support;

use CuyZ\WebZ\Core\Support\Timer;
use PHPUnit\Framework\TestCase;

class TimerTest extends TestCase
{
    public function test_timer()
    {
        $timer = Timer::start();

        usleep(1);

        self::assertGreaterThan(0, $timer->timeInSeconds());
        self::assertGreaterThan(0, $timer->timeInMilliseconds());
    }
}
