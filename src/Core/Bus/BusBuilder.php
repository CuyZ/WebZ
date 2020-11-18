<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Core\Bus;

use CuyZ\WebZ\Core\Bus\Pipeline\Pipeline;
use CuyZ\WebZ\Core\Bus\Pipeline\PipelineMiddleware;
use CuyZ\WebZ\Core\Cache\MemoizationMiddleware;
use CuyZ\WebZ\Core\Cache\SimpleCacheMiddleware;
use CuyZ\WebZ\Core\Event\EventsMiddleware;
use CuyZ\WebZ\Core\Exception\HandleExceptionsMiddleware;
use CuyZ\WebZ\Core\Transport\CallTransportMiddleware;
use CuyZ\WebZ\Core\Transport\Transport;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\SimpleCache\CacheInterface;

final class BusBuilder
{
    private ?CacheInterface $cache = null;
    private ?EventDispatcherInterface $eventDispatcher = null;
    private bool $useMemoization = true;
    private bool $skipCorruptCacheEntries = false;

    /** @var Transport[] */
    private array $transports = [];

    /** @var Middleware[] */
    private array $beforeCall = [];

    public function withCache(CacheInterface $cache): self
    {
        $this->cache = $cache;
        return $this;
    }

    public function skipCorruptCacheEntries(): self
    {
        $this->skipCorruptCacheEntries = true;
        return $this;
    }

    public function withEventDispatcher(EventDispatcherInterface $dispatcher): self
    {
        $this->eventDispatcher = $dispatcher;
        return $this;
    }

    public function withTransport(Transport $transport): self
    {
        $this->transports[] = $transport;
        return $this;
    }

    public function withoutMemoization(): self
    {
        $this->useMemoization = false;
        return $this;
    }

    public function beforeCall(Middleware $middleware): self
    {
        $this->beforeCall[] = $middleware;
        return $this;
    }

    public function build(): Bus
    {
        if (count($this->transports) === 0) {
            throw new NoTransportException();
        }

        $pipeline = new Pipeline();

        $pipeline->append(new HandleExceptionsMiddleware());

        if ($this->eventDispatcher instanceof EventDispatcherInterface) {
            $pipeline->append(new EventsMiddleware($this->eventDispatcher));
        }

        if ($this->cache instanceof CacheInterface) {
            if ($this->useMemoization) {
                $pipeline->append(new MemoizationMiddleware());
            }

            $pipeline->append(new SimpleCacheMiddleware($this->cache, $this->skipCorruptCacheEntries));
        }

        if (count($this->beforeCall) > 0) {
            $pipeline->append(new PipelineMiddleware(new Pipeline($this->beforeCall)));
        }

        // Needs to be very last (the actual call)
        $pipeline->append(new CallTransportMiddleware($this->transports));

        return new Bus($pipeline);
    }
}
