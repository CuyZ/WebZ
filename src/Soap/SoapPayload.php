<?php
/** @noinspection PhpComposerExtensionStubsInspection */
declare(strict_types=1);

namespace CuyZ\WebZ\Soap;

use CuyZ\WebZ\Soap\Exception\MissingSoapMethodException;

final class SoapPayload
{
    public const DEFAULT_OPTIONS = [
        'trace' => true,
        'exceptions' => true,
        'features' => SOAP_SINGLE_ELEMENT_ARRAYS | SOAP_USE_XSI_ARRAY_TYPE,
    ];

    private ?string $method;
    private array $arguments = [];
    private array $options;

    private ?string $wsdl;
    private ?string $location;
    private ?string $uri;

    private function __construct(?string $soapMethod, ?string $wsdl, ?string $location, ?string $uri)
    {
        $this->method = $soapMethod;
        $this->wsdl = $wsdl;
        $this->location = $location;
        $this->uri = $uri;

        $this->options = self::DEFAULT_OPTIONS;
    }

    public static function forNonWsdl(string $location, string $uri, ?string $soapMethod = null): self
    {
        return new self($soapMethod, null, $location, $uri);
    }

    public static function forWsdl(string $wsdl, ?string $soapMethod = null): self
    {
        return new self($soapMethod, $wsdl, null, null);
    }

    public function withLocation(string $location): self
    {
        $this->location = $location;
        return $this;
    }

    public function withMethod(string $method): self
    {
        $this->method = $method;
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

    public function wsdl(): ?string
    {
        return $this->wsdl;
    }

    public function method(): string
    {
        if (null === $this->method) {
            throw new MissingSoapMethodException();
        }

        return $this->method;
    }

    public function arguments(): array
    {
        return $this->arguments;
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
