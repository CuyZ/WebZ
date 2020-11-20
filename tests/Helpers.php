<?php

namespace Tests;

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

class Mocks
{
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
        return new Result(RawResult::ok($data), Timer::zero());
    }

    public static function promiseOk(array $data = []): PromiseInterface
    {
        return new FulfilledPromise(self::resultOk($data));
    }

    public static function resultErr(Exception $exception, array $data = []): Result
    {
        return new Result(RawResult::err($exception, $data), Timer::zero());
    }
}
