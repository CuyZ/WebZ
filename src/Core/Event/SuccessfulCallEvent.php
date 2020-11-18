<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Core\Event;

use CuyZ\WebZ\Core\Result\Result;
use CuyZ\WebZ\Core\WebService;

/**
 * @immutable
 */
final class SuccessfulCallEvent
{
    public WebService $webService;
    public Result $result;

    public function __construct(WebService $webService, Result $result)
    {
        $this->webService = $webService;
        $this->result = $result;
    }
}
