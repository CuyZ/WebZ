<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Core\Http;

use Closure;

final class AutoFactory implements HttpClientFactory
{
    private HttpClientFactory $delegate;

    /**
     * @param HttpClientFactory|Closure|null $factory
     */
    public function __construct($factory = null)
    {
        $this->delegate = $this->wrap($factory);
    }

    public function build(?string $asyncCallHash): HttpClient
    {
        return $this->delegate->build($asyncCallHash);
    }

    /**
     * @param HttpClientFactory|Closure|null $factory
     * @return HttpClientFactory
     */
    private function wrap($factory): HttpClientFactory
    {
        if (null === $factory) {
            return new ClosureFactory(fn(?string $asyncCallHash) => HttpClient::create($asyncCallHash));
        }

        if ($factory instanceof Closure) {
            return new ClosureFactory($factory);
        }

        return $factory;
    }
}
