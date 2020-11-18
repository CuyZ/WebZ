<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Core\Cache;

use CuyZ\WebZ\Core\Bus\Pipeline\Next;
use CuyZ\WebZ\Core\Bus\Middleware;
use CuyZ\WebZ\Core\Result\Result;
use CuyZ\WebZ\Core\WebService;

final class MemoizationMiddleware implements Middleware
{
    /**
     * @var array<string, Result>
     */
    private array $cache = [];

    public function process(WebService $webService, Next $next): Result
    {
        if (!$webService instanceof WithCache) {
            return $next($webService);
        }

        if ($webService->cacheLifetime() <= 0) {
            return $next($webService);
        }

        return $this->cache[$webService->getPayloadHash()] ??= $next($webService);
    }
}
