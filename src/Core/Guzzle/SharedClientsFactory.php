<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Core\Guzzle;

use GuzzleHttp\Client;

final class SharedClientsFactory implements GuzzleClientFactory
{
    /** @var Client[] */
    private array $clients = [];

    public function build(?string $asyncCallHash): Client
    {
        if (null === $asyncCallHash) {
            return new Client();
        }

        return $this->clients[$asyncCallHash] ??= new Client();
    }
}
