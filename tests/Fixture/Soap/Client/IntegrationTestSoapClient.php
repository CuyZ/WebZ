<?php
/** @noinspection PhpComposerExtensionStubsInspection */
declare(strict_types=1);

namespace CuyZ\WebZ\Tests\Fixture\Soap\Client;

use Closure;
use CuyZ\WebZ\Tests\Fixture\Soap\Server\FakeSoapServerClass;
use SoapClient;

final class IntegrationTestSoapClient
{
    public static function make(array $options = []): SoapClient
    {
        ini_set('default_socket_timeout', '1');

        $options['cache_wsdl'] = WSDL_CACHE_NONE;

        return new SoapClient(FakeSoapServerClass::WSDL, $options);
    }

    public static function factory(array $options = [], ?Closure $withClient = null): Closure
    {
        return function () use ($withClient, $options) {
            $client = self::make($options);

            if (is_callable($withClient)) {
                $withClient($client);
            }

            return $client;
        };
    }
}
