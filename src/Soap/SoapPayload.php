<?php
/** @noinspection PhpComposerExtensionStubsInspection */
declare(strict_types=1);

namespace CuyZ\WebZ\Soap;

use CuyZ\WebZ\Soap\Exception\MissingSoapActionException;
use SoapHeader;

final class SoapPayload
{
    public const DEFAULT_OPTIONS = [
        'exceptions' => true,
        'features' => SOAP_SINGLE_ELEMENT_ARRAYS | SOAP_USE_XSI_ARRAY_TYPE,
    ];

    private ?string $action;
    private array $arguments = [];
    private array $options;

    /** @var SoapHeader[] */
    private array $headers = [];

    private ?string $wsdl;
    private ?string $location;
    private ?string $uri;
    private string $httpMethod = 'POST';

    private function __construct(?string $soapAction, ?string $wsdl, ?string $location, ?string $uri)
    {
        $this->action = $soapAction;
        $this->wsdl = $wsdl;
        $this->location = $location;
        $this->uri = $uri;

        $this->options = self::DEFAULT_OPTIONS;
    }

    public static function forNonWsdl(string $location, string $uri, ?string $soapAction = null): self
    {
        return new self($soapAction, null, $location, $uri);
    }

    public static function forWsdl(string $wsdl, ?string $soapAction = null): self
    {
        return new self($soapAction, $wsdl, null, null);
    }

    public function withLocation(string $location): self
    {
        $this->location = $location;
        return $this;
    }

    public function withAction(string $action): self
    {
        $this->action = $action;
        return $this;
    }

    public function withArguments(array $arguments): self
    {
        $this->arguments = $arguments;
        return $this;
    }

    public function withOptions(array $options): self
    {
        $this->options = $options;
        return $this;
    }

    public function withHeader(SoapHeader $header): self
    {
        $this->headers[] = $header;
        return $this;
    }

    public function withHttpMethod(string $httpMethod): self
    {
        $this->httpMethod = $httpMethod;
        return $this;
    }

    public function wsdl(): ?string
    {
        return $this->wsdl;
    }

    public function action(): string
    {
        if (null === $this->action) {
            throw new MissingSoapActionException();
        }

        return $this->action;
    }

    public function arguments(): array
    {
        return $this->arguments;
    }

    /**
     * @return SoapHeader[]
     */
    public function headers(): array
    {
        return $this->headers;
    }

    public function httpMethod(): string
    {
        return $this->httpMethod;
    }

    public function options(): array
    {
        $options = $this->options;

        if (null !== $this->location) {
            $options['location'] = $this->location;
        }

        if (null !== $this->uri) {
            $options['uri'] = $this->uri;
        }

        return $options;
    }
}
