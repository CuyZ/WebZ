<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Tests\Fixture;

use CuyZ\WebZ\Core\Result\RawResult;
use CuyZ\WebZ\Core\Result\Result;
use CuyZ\WebZ\Core\Support\Timer;
use Exception;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;

final class Mocks
{
    public static function timer(): Timer
    {
        $timer = Timer::start();
        $timer->stop();

        return $timer;
    }

    public static function resultOk(array $data = []): Result
    {
        return new Result(RawResult::ok($data), self::timer());
    }

    public static function promiseOk(array $data = []): PromiseInterface
    {
        return new FulfilledPromise(self::resultOk($data));
    }

    public static function resultErr(Exception $exception, array $data = []): Result
    {
        return new Result(RawResult::err($exception, $data), self::timer());
    }
}
