<?php

namespace CuyZ\WebZ\Soap\Client;

use CuyZ\WebZ\Soap\SoapPayload;
use GuzzleHttp\Promise\PromiseInterface;

interface SoapSender
{
    public function send(SoapPayload $payload, ?string $payloadGroupHash = null): PromiseInterface;
}
