<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Core\Exception;

use CuyZ\WebZ\Core\Bus\Middleware;
use CuyZ\WebZ\Core\Bus\Pipeline\Next;
use CuyZ\WebZ\Core\Result\Result;
use CuyZ\WebZ\Core\WebService;
use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\RejectedPromise;

final class HandleExceptionsMiddleware implements Middleware
{
    public function process(WebService $webService, Next $next): PromiseInterface
    {
        try {
            return $next($webService)->then(
                function (Result $result) use ($webService) {
                    $e = $result->exception();

                    if ($e instanceof Exception) {
                        return $this->onException($webService, $e);
                    }

                    return $result;
                },
                fn(Exception $e) => $this->onException($webService, $e)
            );
        } catch (Exception $e) {
            return $this->onException($webService, $e);
        }
    }

    private function onException(WebService $webService, Exception $e): PromiseInterface
    {
        try {
            if ($webService instanceof HandlesExceptions) {
                $webService->onException($e);
            }
        } catch (Exception $e) {
        }

        return new RejectedPromise($e);
    }
}
