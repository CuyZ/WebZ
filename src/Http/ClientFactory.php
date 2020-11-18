<?php

namespace CuyZ\WebZ\Http;

use GuzzleHttp\Client;

interface ClientFactory
{
    public function build(): Client;
}
