<?php
/** @noinspection PhpComposerExtensionStubsInspection */
declare(strict_types=1);

namespace CuyZ\WebZ\Tests\Fixture\Soap\Client;

use SoapClient;
use SoapFault;

final class UnitTestSoapClient extends SoapClient
{
    private string $lastRequest;
    private array $responses;

    public function __construct(array $responses = [])
    {
        parent::__construct(
            null,
            [
                'location' => 'mock',
                'uri' => 'mock',
            ]
        );

        $this->responses = $responses;
    }

    public function __soapCall($function_name, $arguments, $options = null, $input_headers = null, &$output_headers = null)
    {
        $response = $this->responses[$function_name] ?? null;

        if ($response instanceof SoapFault) {
            throw $response;
        }

        return $response;
    }

    public function setLastRequest($lastRequest)
    {
        $this->lastRequest = $lastRequest;
    }

    public function __getLastRequest()
    {
        if (isset($this->lastRequest)) {
            return $this->lastRequest;
        }

        return null;
    }
}
