<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Core\Http;

use Closure;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

use function is_callable;

final class HttpClient
{
    /** @var HttpClient[] */
    private static array $clients = [];

    private Client $guzzle;

    /**
     * @psalm-var (Closure(RequestInterface $request):void)|null
     * @var Closure|null
     */
    private ?Closure $onRequestListener = null;

    public static function create(?string $asyncCallHash = null): HttpClient
    {
        if (null === $asyncCallHash) {
            return new self();
        }

        return self::$clients[$asyncCallHash] ??= new self();
    }

    /**
     * @param ResponseInterface|RequestException ...$responses
     * @return HttpClient
     */
    public static function mock(...$responses): HttpClient
    {
        $mock = new MockHandler($responses);

        $handlerStack = HandlerStack::create($mock);

        return new self(['handler' => $handlerStack], false);
    }

    private function __construct(array $config = [], bool $withListener = true)
    {
        if ($withListener) {
            /** @var HandlerStack|callable $handler */
            $handler = $config['handler'] ?? HandlerStack::create();

            if (!$handler instanceof HandlerStack) {
                $handler = HandlerStack::create($handler);
            }

            $handler->push(Middleware::tap(
                function (RequestInterface $req): void {
                    if (is_callable($this->onRequestListener)) {
                        ($this->onRequestListener)($req);
                    }
                }
            ));

            $config['handler'] = $handler;
        }

        $this->guzzle = new Client($config);
    }

    /**
     * @param Closure(RequestInterface $request):void $callback
     */
    public function onRequest(Closure $callback): void
    {
        $this->onRequestListener = $callback;
    }

    public function sendAsync(RequestInterface $request): PromiseInterface
    {
        return $this->guzzle->sendAsync($request);
    }

    /**
     * @param string $method
     * @param string|UriInterface $uri
     * @param array $options
     * @return PromiseInterface
     */
    public function requestAsync(string $method, $uri, array $options = []): PromiseInterface
    {
        return $this->guzzle->requestAsync($method, $uri, $options);
    }
}
