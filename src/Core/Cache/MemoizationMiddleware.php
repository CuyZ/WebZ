<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Core\Cache;

use CuyZ\WebZ\Core\Bus\Middleware;
use CuyZ\WebZ\Core\Bus\Pipeline\Next;
use CuyZ\WebZ\Core\Result\Result;
use CuyZ\WebZ\Core\WebService;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;

final class MemoizationMiddleware implements Middleware
{
    /**
     * @var array<string, Result>
     */
    private array $cache = [];

    public function process(WebService $webService, Next $next): PromiseInterface
    {
        if (!$webService instanceof WithCache) {
            return $next($webService);
        }

        if ($webService->cacheLifetime() <= 0) {
            return $next($webService);
        }

        $hash = $webService->getPayloadHash();

        if (array_key_exists($hash, $this->cache)) {
            return new FulfilledPromise($this->cache[$hash]);
        }

        return $next($webService)
            ->then(function (Result $result) use ($hash): Result {
                $this->cache[$hash] = $result;

                return $result;
            });
    }
}
