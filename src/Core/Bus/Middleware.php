<?php

namespace CuyZ\WebZ\Core\Bus;

use CuyZ\WebZ\Core\Bus\Pipeline\Next;
use CuyZ\WebZ\Core\WebService;
use GuzzleHttp\Promise\PromiseInterface;

interface Middleware
{
    public function process(WebService $webService, Next $next): PromiseInterface;
}
