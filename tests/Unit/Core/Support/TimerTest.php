<?php

namespace CuyZ\WebZ\Tests\Unit\Core\Support;

use CuyZ\WebZ\Core\Support\Timer;
use PHPUnit\Framework\TestCase;

/**
 * @covers \CuyZ\WebZ\Core\Support\Timer
 */
class TimerTest extends TestCase
{
    public function test_timer_with_auto_stop()
    {
        $timer = Timer::start();

        usleep(1);

        self::assertGreaterThan(0, $timer->timeInSeconds());
        self::assertGreaterThan(0, $timer->timeInMilliseconds());
    }

    public function test_timer_with_manual_stop()
    {
        $timer = Timer::start();

        usleep(1);

        $timer->stop();

        self::assertGreaterThan(0, $timer->timeInSeconds());
        self::assertGreaterThan(0, $timer->timeInMilliseconds());
    }

    public function test_final_time_is_memoized()
    {
        $timer = Timer::start();

        usleep(1);

        $seconds1 = $timer->timeInSeconds();
        $milliseconds1 = $timer->timeInMilliseconds();

        usleep(1);
        $timer->stop();

        $seconds2 = $timer->timeInSeconds();
        $milliseconds2 = $timer->timeInMilliseconds();

        self::assertSame($seconds1, $seconds2);
        self::assertSame($milliseconds1, $milliseconds2);
    }
}
