<?php

namespace CuyZ\WebZ\Core\Guzzle;

interface GuzzleClientFactory
{
    public function build(?string $asyncCallHash): HttpClient;
}
