<?php
/** @noinspection PhpComposerExtensionStubsInspection */
declare(strict_types=1);

namespace CuyZ\WebZ\Soap;

use Closure;
use CuyZ\WebZ\Core\Result\RawResult;
use CuyZ\WebZ\Core\Support\Arr;
use CuyZ\WebZ\Core\Transport\Transport;
use CuyZ\WebZ\Soap\Exception\SoapExtensionNotInstalledException;
use SoapClient;
use SoapFault;

/**
 * @psalm-type SoapFactory = Closure(SoapPayload $payload):SoapClient
 */
final class SoapTransport implements Transport
{
    /**
     * @psalm-var SoapFactory
     * @phpstan-var Closure
     */
    private $factory;

    /**
     * @param ClientFactory|Closure|null $factory
     */
    public function __construct($factory = null)
    {
        /**
         * The `extension_loaded` function must no be imported
         * or prefixed with a / so that the unit test works.
         * @see tests/Platform/SoapTest.php
         */
        if (!extension_loaded('soap')) {
            throw new SoapExtensionNotInstalledException(); // @codeCoverageIgnore
        }

        if ($factory instanceof ClientFactory) {
            $factory = fn(SoapPayload $payload): SoapClient => $factory->build($payload);
        }

        if (!$factory instanceof Closure) {
            $factory = fn(SoapPayload $payload): SoapClient => new SoapClient($payload->wsdl(), $payload->options());
        }

        $this->factory = $factory;
    }

    /**
     * @param SoapPayload|object $payload
     * @return RawResult
     */
    public function send(object $payload): ?RawResult
    {
        if (!$payload instanceof SoapPayload) {
            return null;
        }

        $client = $this->makeClient($payload);

        $soapFault = null;
        $raw = null;

        try {
            /** @var mixed|SoapFault $raw */
            $raw = $client->__soapCall($payload->method(), $payload->arguments());

            if ($raw instanceof SoapFault) {
                $soapFault = $raw;
            }
        } catch (SoapFault $soapFault) {
            $raw = $soapFault;
        }

        if ($raw instanceof SoapFault) {
            $raw = $raw->getMessage();
        }

        if (!is_array($raw) && !is_object($raw)) {
            $raw = ['value' => $raw];
        }

        $raw = Arr::castToArray($raw);

        if ($soapFault instanceof SoapFault) {
            $result = RawResult::err($soapFault, $raw);
        } else {
            $result = RawResult::ok($raw);
        }

        return $result
            ->withRequestTrace($client->__getLastRequest())
            ->withResponseTrace($client->__getLastResponse());
    }

    private function makeClient(SoapPayload $payload): SoapClient
    {
        return ($this->factory)($payload);
    }
}
