<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Core\Cache;

use CuyZ\WebZ\Core\Bus\Middleware;
use CuyZ\WebZ\Core\Bus\Pipeline\Next;
use CuyZ\WebZ\Core\Result\Result;
use CuyZ\WebZ\Core\WebService;
use Psr\SimpleCache\CacheInterface;

final class SimpleCacheMiddleware implements Middleware
{
    private CacheInterface $cache;
    private bool $skipCorruptCacheEntries;

    public function __construct(CacheInterface $cache, bool $skipCorruptCacheEntries = false)
    {
        $this->cache = $cache;
        $this->skipCorruptCacheEntries = $skipCorruptCacheEntries;
    }

    public function process(WebService $webService, Next $next): Result
    {
        if (!$webService instanceof WithCache) {
            return $next($webService);
        }

        $ttl = $webService->cacheLifetime();

        if (0 === $ttl) {
            return $next($webService);
        }

        $hash = $webService->getPayloadHash();

        if ($this->cache->has($hash)) {
            $result = $this->cache->get($hash);

            if (!$result instanceof Result) {
                if (true === $this->skipCorruptCacheEntries) {
                    return $next($webService);
                }

                throw new CorruptCacheEntryException($hash);
            }

            return $result->markAsComingFromCache();
        }

        $result = $next($webService);

        if (!$result->isFromCache()) {
            $this->cache->set($hash, $result, $ttl);
        }

        return $result;
    }
}
