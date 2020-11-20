<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Core\Bus\Pipeline;

use Closure;
use CuyZ\WebZ\Core\WebService;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * @psalm-type Invoked = Closure(WebService $webService):PromiseInterface
 */
final class Next
{
    /**
     * @phpstan-var Closure
     * @psalm-var Invoked
     */
    private Closure $callback;

    /**
     * @phpstan-param Closure $callback
     * @psalm-param Invoked $callback
     *
     * @param Closure $callback
     */
    public function __construct(Closure $callback)
    {
        $this->callback = $callback;
    }

    public function __invoke(WebService $webService): PromiseInterface
    {
        return ($this->callback)($webService);
    }
}
