<?php
/** @noinspection PhpComposerExtensionStubsInspection */

namespace CuyZ\WebZ\Soap;

use SoapClient;

interface ClientFactory
{
    public function build(SoapPayload $payload): SoapClient;
}
