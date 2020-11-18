<?php

namespace CuyZ\WebZ\Http;

use Symfony\Contracts\HttpClient\HttpClientInterface;

interface ClientFactory
{
    public function build(): HttpClientInterface;
}
