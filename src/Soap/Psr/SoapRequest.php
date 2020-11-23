<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Soap\Psr;

final class SoapRequest
{
    private string $endpoint;
    private string $soapAction;
    private int $soapVersion;
    private string $soapMessage;

    public function __construct(string $endpoint, string $soapAction, int $soapVersion, string $soapMessage)
    {
        $this->endpoint = $endpoint;
        $this->soapAction = $soapAction;
        $this->soapVersion = $soapVersion;
        $this->soapMessage = $soapMessage;
    }

    public function endpoint(): string
    {
        return $this->endpoint;
    }

    public function soapAction(): string
    {
        return $this->soapAction;
    }

    public function soapVersion(): int
    {
        return $this->soapVersion;
    }

    public function soapMessage(): string
    {
        return $this->soapMessage;
    }
}
