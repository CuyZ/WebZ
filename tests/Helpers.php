<?php

namespace Tests;

use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
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
}
