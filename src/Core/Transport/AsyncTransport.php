<?php

namespace CuyZ\WebZ\Core\Transport;

use GuzzleHttp\Promise\PromiseInterface;

interface AsyncTransport extends Transport
{
    public function sendAsync(object $payload, ?string $asyncCallHash): ?PromiseInterface;
}
