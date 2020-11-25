<?php

namespace CuyZ\WebZ\Core\Guzzle;

use GuzzleHttp\Client;

interface GuzzleClientFactory
{
    public function build(?string $asyncCallHash): Client;
}
