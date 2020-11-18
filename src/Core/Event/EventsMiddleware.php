<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Core\Event;

use CuyZ\WebZ\Core\Bus\Pipeline\Next;
use CuyZ\WebZ\Core\Bus\Middleware;
use CuyZ\WebZ\Core\Result\Result;
use CuyZ\WebZ\Core\WebService;
use Exception;
use Psr\EventDispatcher\EventDispatcherInterface;

final class EventsMiddleware implements Middleware
{
    private EventDispatcherInterface $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function process(WebService $webService, Next $next): Result
    {
        $this->dispatcher->dispatch(new BeforeCallEvent($webService));

        try {
            $result = $next($webService);

            $e = $result->exception();

            if ($e instanceof Exception) {
                throw $e;
            }
        } catch (Exception $e) {
            $this->dispatcher->dispatch(new FailedCallEvent($webService, $e));

            throw $e;
        }

        $this->dispatcher->dispatch(new SuccessfulCallEvent($webService, $result));

        return $result;
    }
}
