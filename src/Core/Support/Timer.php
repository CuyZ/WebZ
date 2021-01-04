<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Core\Support;

final class Timer
{
    private float $startTime;
    private ?float $stopTime = null;
    private ?float $seconds = null;

    private function __construct()
    {
        $this->startTime = $this->microtime();
    }

    public static function start(): Timer
    {
        return new Timer();
    }

    public function stop(): void
    {
        $this->stopTime = $this->microtime();
    }

    public function timeInSeconds(): float
    {
        if (null === $this->stopTime) {
            $this->stop();
        }

        /** @psalm-suppress RedundantCast */
        return $this->seconds ??= ((float)$this->stopTime) - $this->startTime;
    }

    public function timeInMilliseconds(): float
    {
        return $this->timeInSeconds() * 1000;
    }

    private function microtime(): float
    {
        return microtime(true);
    }
}
