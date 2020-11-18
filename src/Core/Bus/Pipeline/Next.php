<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Core\Bus\Pipeline;

use Closure;
use CuyZ\WebZ\Core\Result\Result;
use CuyZ\WebZ\Core\WebService;

/**
 * @psalm-type Invoked = Closure(WebService $webService):Result
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

    public function __invoke(WebService $webService): Result
    {
        return ($this->callback)($webService);
    }
}
