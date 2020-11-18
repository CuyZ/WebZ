<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Tests\Fixture\Middleware;

use CuyZ\WebZ\Core\Bus\Middleware;
use CuyZ\WebZ\Core\Bus\Pipeline\Next;
use CuyZ\WebZ\Core\Result\Result;
use CuyZ\WebZ\Core\WebService;

final class IndexMiddleware implements Middleware
{
    private int $index;

    public function __construct(int $index)
    {
        $this->index = $index;
    }

    public function process(WebService $webService, Next $next): Result
    {
        $result = $next($webService);

        $raw = $result->data();
        $raw[] = $this->index;

        return $result->withData($raw);
    }
}
