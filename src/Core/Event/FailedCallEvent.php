<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Core\Event;

use CuyZ\WebZ\Core\WebService;
use Exception;

/**
 * @immutable
 */
final class FailedCallEvent
{
    public WebService $webService;
    public Exception $exception;

    public function __construct(WebService $webService, Exception $exception)
    {
        $this->webService = $webService;
        $this->exception = $exception;
    }
}
