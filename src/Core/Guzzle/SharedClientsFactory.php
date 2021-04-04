<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Core\Guzzle;

final class SharedClientsFactory implements GuzzleClientFactory
{
    /** @var HttpClient[] */
    private array $clients = [];

    public function build(?string $asyncCallHash): HttpClient
    {
        if (null === $asyncCallHash) {
            return new HttpClient();
        }

        return $this->clients[$asyncCallHash] ??= new HttpClient();
    }
}
