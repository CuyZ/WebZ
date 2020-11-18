<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Core\Bus;

use CuyZ\WebZ\Core\Bus\Pipeline\Pipeline;
use CuyZ\WebZ\Core\Exception\WebZException;
use CuyZ\WebZ\Core\WebService;
use Exception;

final class Bus
{
    private Pipeline $pipeline;

    public function __construct(Pipeline $pipeline)
    {
        $this->pipeline = $pipeline;
    }

    public static function builder(): BusBuilder
    {
        return new BusBuilder();
    }

    /**
     * @param WebService $webService
     * @return mixed
     */
    public function call(WebService $webService)
    {
        $result = $this->pipeline->dispatch($webService);

        return $webService->parse($result->data());
    }
}
