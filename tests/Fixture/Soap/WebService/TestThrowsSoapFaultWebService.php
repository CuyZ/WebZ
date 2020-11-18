<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Tests\Fixture\Soap\WebService;

use CuyZ\WebZ\Core\WebService;
use CuyZ\WebZ\Soap\SoapPayload;
use CuyZ\WebZ\Tests\Fixture\Soap\Server\FakeSoapServerClass;

final class TestThrowsSoapFaultWebService extends WebService
{
    private string $faultCode;
    private string $faultString;

    public function __construct(string $faultCode, string $faultString)
    {
        $this->faultCode = $faultCode;
        $this->faultString = $faultString;
    }

    protected function payload(): object
    {
        return SoapPayload::forWsdl(FakeSoapServerClass::WSDL, 'throwSoapFault')
            ->withArguments([$this->faultCode, $this->faultString]);
    }

    public function parse(array $data)
    {
        return $data;
    }
}
