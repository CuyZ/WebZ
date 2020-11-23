<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Tests\Fixture\Soap\WebService;

use CuyZ\WebZ\Core\WebService;
use CuyZ\WebZ\Soap\SoapPayload;
use CuyZ\WebZ\Tests\Fixture\Soap\Server\FakeSoapServerClass;

final class TestReturnsInputWebService extends WebService
{
    private $input;

    public function __construct($input)
    {
        $this->input = $input;
    }

    protected function payload(): object
    {
        return SoapPayload::forWsdl(FakeSoapServerClass::WSDL_URI, 'returnValue')
            ->withArguments([$this->input]);
    }

    public function parse(array $data)
    {
        return $data;
    }
}
