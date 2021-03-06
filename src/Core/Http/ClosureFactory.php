<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Core\Http;

use Closure;

final class ClosureFactory implements HttpClientFactory
{
    private Closure $closure;

    public function __construct(Closure $closure)
    {
        $this->closure = $closure;
    }

    public function build(?string $asyncCallHash): HttpClient
    {
        return ($this->closure)($asyncCallHash);
    }
}
