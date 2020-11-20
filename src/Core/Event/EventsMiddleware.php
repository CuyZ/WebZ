<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Core\Event;

use CuyZ\WebZ\Core\Bus\Middleware;
use CuyZ\WebZ\Core\Bus\Pipeline\Next;
use CuyZ\WebZ\Core\Result\Result;
use CuyZ\WebZ\Core\WebService;
use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

final class EventsMiddleware implements Middleware
{
    private EventDispatcherInterface $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function process(WebService $webService, Next $next): PromiseInterface
    {
        $this->dispatcher->dispatch(new BeforeCallEvent($webService));

        try {
            return $next($webService)
                ->then(
                    fn(Result $result): Result => $this->then($webService, $result),
                    function (Exception $e) use ($webService): void {
                        $this->dispatchError($webService, $e);
                    }
                );
        } catch (Exception $e) {
            $this->dispatchError($webService, $e);
        }
    }

    private function then(WebService $webService, Result $result): Result
    {
        $e = $result->exception();

        if ($e instanceof Exception) {
            $this->dispatchError($webService, $e);
        }

        $this->dispatcher->dispatch(new SuccessfulCallEvent($webService, $result));

        return $result;
    }

    /**
     * @psalm-return never-returns
     *
     * @param WebService $webService
     * @param Exception $e
     * @throws Exception
     */
    private function dispatchError(WebService $webService, Exception $e): void
    {
        $this->dispatcher->dispatch(new FailedCallEvent($webService, $e));

        throw $e;
    }
}
