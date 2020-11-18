<?php

namespace CuyZ\WebZ\Core\Bus;

use CuyZ\WebZ\Core\Bus\Pipeline\Next;
use CuyZ\WebZ\Core\Result\Result;
use CuyZ\WebZ\Core\WebService;

interface Middleware
{
    public function process(WebService $webService, Next $next): Result;
}
