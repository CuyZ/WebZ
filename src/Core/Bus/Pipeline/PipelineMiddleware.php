<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Core\Bus\Pipeline;

use CuyZ\WebZ\Core\Bus\Middleware;
use CuyZ\WebZ\Core\WebService;
use GuzzleHttp\Promise\PromiseInterface;

final class PipelineMiddleware implements Middleware
{
    private Pipeline $pipeline;

    public function __construct(Pipeline $pipeline)
    {
        $this->pipeline = $pipeline;
    }

    public function process(WebService $webService, Next $next): PromiseInterface
    {
        $this->pipeline->append(new class($next) implements Middleware {
            private Next $last;

            public function __construct(Next $last)
            {
                $this->last = $last;
            }

            public function process(WebService $webService, Next $next): PromiseInterface
            {
                return ($this->last)($webService);
            }
        });

        return $this->pipeline->dispatch($webService);
    }
}
