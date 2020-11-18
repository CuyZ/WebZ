<?php

namespace CuyZ\WebZ\Core\Transport;

use CuyZ\WebZ\Core\Result\RawResult;

interface Transport
{
    public function send(object $payload): ?RawResult;
}
