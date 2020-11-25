<?php
/** @noinspection PhpComposerExtensionStubsInspection */
declare(strict_types=1);

namespace CuyZ\WebZ\Soap\Psr;

use CuyZ\WebZ\Soap\SoapPayload;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use SoapFault;

final class SoapToPsrConverter
{
    private const SOAP11 = '1.1';
    private const SOAP12 = '1.2';

    private SoapInterpreter $interpreter;
    private SoapPayload $payload;

    public function __construct(SoapPayload $payload)
    {
        $this->payload = $payload;
        $this->interpreter = new SoapInterpreter($payload);
    }

    public function toRequest(): RequestInterface
    {
        $soapRequest = $this->interpreter->request();

        $httpMethod = $this->payload->httpMethod();

        $soapVersion = $soapRequest->soapVersion() === 1
            ? self::SOAP11
            : self::SOAP12;

        $endpoint = $soapRequest->endpoint();
        $soapAction = $soapRequest->soapAction();

        /**
         * Guzzle checks the resource itself but the docblock
         * does not allow the error value `false`.
         * @var resource $resource
         */
        $resource = fopen('php://temp', 'r+');
        $soapMessage = new Stream($resource);

        if ($httpMethod === 'POST') {
            $soapMessage->write($soapRequest->soapMessage());
            $soapMessage->rewind();
        }

        return new Request(
            $httpMethod,
            $endpoint,
            $this->requestHeaders($soapVersion, $soapMessage, $soapAction, $httpMethod),
            $soapMessage,
        );
    }

    /**
     * @param ResponseInterface $response
     * @return mixed
     * @throws SoapFault
     */
    public function fromResponse(ResponseInterface $response)
    {
        return $this->interpreter->response($response->getBody()->getContents());
    }

    private function requestHeaders(
        string $soapVersion,
        StreamInterface $soapMessage,
        string $soapAction,
        string $httpMethod
    ): array {
        if ($soapVersion == self::SOAP11) {
            return [
                'Content-Length' => $soapMessage->getSize(),
                'SOAPAction' => $soapAction,
                'Content-Type' => 'text/xml; charset="utf-8"',
            ];
        }

        if ($httpMethod == 'POST') {
            return [
                'Content-Length' => $soapMessage->getSize(),
                'Content-Type' => 'application/soap+xml; charset="utf-8"' . '; action="' . $soapAction . '"',
            ];
        }

        return [
            'Accept' => 'application/soap+xml',
        ];
    }
}
