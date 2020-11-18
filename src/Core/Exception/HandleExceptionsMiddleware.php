<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Core\Exception;

use CuyZ\WebZ\Core\Bus\Pipeline\Next;
use CuyZ\WebZ\Core\Bus\Middleware;
use CuyZ\WebZ\Core\Result\Result;
use CuyZ\WebZ\Core\WebService;
use Exception;

final class HandleExceptionsMiddleware implements Middleware
{
    public function process(WebService $webService, Next $next): Result
    {
        try {
            $result = $next($webService);

            $e = $result->exception();

            if ($e instanceof Exception) {
                throw $e;
            }
        } catch (Exception $e) {
            if ($webService instanceof HandlesExceptions) {
                $webService->onException($e);
            }

            throw $e;
        }

        return $result;
    }
}
