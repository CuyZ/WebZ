<?php
/** @noinspection PhpComposerExtensionStubsInspection */
declare(strict_types=1);

namespace CuyZ\WebZ\Tests\Fixture\Soap\Server;

use CuyZ\WebZ\Tests\Fixture\Server\TestServer;
use CuyZ\WebZ\Tests\Fixture\Utils;
use Exception;
use SoapFault;

final class FakeSoapServerClass
{
    public const URI = TestServer::DOMAIN . '/soap';
    public const TMP_DIR = __DIR__ . '/../../../../tmp';
    public const WSDL = self::TMP_DIR . '/wsdl.xml';

    /**
     * @param mixed $value
     * @return mixed
     */
    public function returnValue($value)
    {
        return $value;
    }

    /**
     * @param string $input
     * @return string
     * @throws Exception
     */
    public function randomValue(string $input): string
    {
        return Utils::random($input);
    }

    /**
     * @param string|null $faultCode
     * @param string|null $faultString
     * @return string
     * @throws SoapFault
     */
    public function throwSoapFault(?string $faultCode = null, ?string $faultString = null)
    {
        throw new SoapFault($faultCode ?? '', $faultString ?? '');

        return 'useless string to make the SoapFault work';
    }

    public function doNothing(): void
    {
        //
    }
}
