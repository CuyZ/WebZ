<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Core\Guzzle;

use Closure;
use GuzzleHttp\Client;

final class ClosureFactory implements GuzzleClientFactory
{
    private Closure $closure;

    public function __construct(Closure $closure)
    {
        $this->closure = $closure;
    }

    /**
     * @param string|null $payloadGroupHash
     * @return Client
     */
    public function build(?string $payloadGroupHash): Client
    {
        return ($this->closure)($payloadGroupHash);
    }
}
