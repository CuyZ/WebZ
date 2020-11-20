<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Core\Bus\Pipeline;

use CuyZ\WebZ\Core\Bus\Middleware;
use CuyZ\WebZ\Core\WebService;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\RejectedPromise;

final class Pipeline
{
    /** @var Middleware[] */
    private array $middlewares;

    /**
     * @param Middleware[] $middlewares
     */
    public function __construct(array $middlewares = [])
    {
        $this->middlewares = $middlewares;
    }

    public function append(Middleware $middleware): void
    {
        $this->middlewares[] = $middleware;
    }

    public function prepend(Middleware $middleware): void
    {
        array_unshift($this->middlewares, $middleware);
    }

    public function dispatch(WebService $webService): PromiseInterface
    {
        $resolved = $this->resolve(0);

        return $resolved($webService);
    }

    private function resolve(int $index): Next
    {
        if (!isset($this->middlewares[$index])) {
            return new Next(function (WebService $webService): PromiseInterface {
                throw new StackExhaustedException();
            });
        }

        return new Next(function (WebService $webService) use ($index): PromiseInterface {
            $middleware = $this->middlewares[$index];

            try {
                return $middleware->process($webService, $this->resolve($index + 1));
            } catch (\Exception $e) {
                return new RejectedPromise($e);
            }
        });
    }
}
