<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Core\Event;

use CuyZ\WebZ\Core\WebService;

/**
 * @immutable
 */
final class BeforeCallEvent
{
    public WebService $webService;

    public function __construct(WebService $webService)
    {
        $this->webService = $webService;
    }
}
