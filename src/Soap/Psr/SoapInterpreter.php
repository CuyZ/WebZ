<?php
/** @noinspection PhpComposerExtensionStubsInspection */
declare(strict_types=1);

namespace CuyZ\WebZ\Soap\Psr;

use CuyZ\WebZ\Soap\SoapPayload;
use SoapClient;
use SoapFault;
use SoapHeader;

/**
 * This class is used to convert the input and output into XML
 * using the native PHP Soap client.
 */
final class SoapInterpreter extends SoapClient
{
    private string $soapLocation = '';
    private string $soapRequest = '';
    private ?string $soapResponse = null;
    private string $soapAction = '';
    private int $soapVersion = 1;
    private SoapPayload $payload;

    public function __construct(SoapPayload $payload)
    {
        $options = $payload->options();

        unset($options['login']);
        unset($options['password']);
        unset($options['proxy_host']);
        unset($options['proxy_port']);
        unset($options['proxy_login']);
        unset($options['proxy_password']);
        unset($options['local_cert']);
        unset($options['passphrase']);
        unset($options['authentication']);
        unset($options['compression']);
        unset($options['trace']);
        unset($options['connection_timeout']);
        unset($options['user_agent']);
        unset($options['stream_context']);
        unset($options['keep_alive']);
        unset($options['ssl_method']);

        parent::__construct($payload->wsdl(), $options);

        $this->payload = $payload;
    }

    public function __doRequest($request, $location, $action, $version, $one_way = 0)
    {
        if (null !== $this->soapResponse) {
            return $this->soapResponse;
        }

        $this->soapLocation = $location;
        $this->soapAction = $action;
        $this->soapVersion = $version;
        $this->soapRequest = $request;

        // This disables the real soap call
        return '';
    }

    public function request(): SoapRequest
    {
        $headers = $this->payload->headers();

        if (count($headers) === 0) {
            /**
             * This avoids an empty `<SOAP-ENV:Header/>` tag.
             *
             * @phpstan-var array|SoapHeader $headers
             */
            $headers = null;
        }

        $this->__soapCall(
            $this->payload->action(),
            $this->payload->arguments(),
            $this->payload->options(),
            $headers
        );

        return new SoapRequest($this->soapLocation, $this->soapAction, $this->soapVersion, $this->soapRequest);
    }

    /**
     * @param string $response
     * @return mixed
     */
    public function response(string $response)
    {
        $this->soapResponse = $response;

        try {
            $response = $this->__soapCall($this->payload->action(), []);
        } catch (SoapFault $fault) {
            $this->soapResponse = null;

            throw $fault;
        }

        $this->soapResponse = null;

        return $response;
    }
}
