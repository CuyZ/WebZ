<?php

namespace CuyZ\WebZ\Core\Http;

interface HttpClientFactory
{
    public function build(?string $asyncCallHash): HttpClient;
}
