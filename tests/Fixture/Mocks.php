<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Tests\Fixture;

use Closure;
use CuyZ\WebZ\Core\Result\RawResult;
use CuyZ\WebZ\Core\Result\Result;
use CuyZ\WebZ\Core\Support\Timer;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;

final class Mocks
{
    public static function timer(): Timer
    {
        $timer = Timer::start();
        $timer->stop();

        return $timer;
    }

    /**
     * @param ResponseInterface|RequestException ...$responses
     * @return Client
     */
    public static function httpClient(...$responses)
    {
        $mock = new MockHandler($responses);

        $handlerStack = HandlerStack::create($mock);

        return new Client(['handler' => $handlerStack]);
    }

    /**
     * @param ResponseInterface|RequestException ...$responses
     * @return Closure
     */
    public static function httpClientFactory(...$responses): Closure
    {
        return fn() => self::httpClient($responses);
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
