<?php
/** @noinspection PhpComposerExtensionStubsInspection */
declare(strict_types=1);

namespace CuyZ\WebZ\Soap;

use CuyZ\WebZ\Core\Result\RawResult;
use CuyZ\WebZ\Core\Transport\AsyncTransport;
use CuyZ\WebZ\Core\Transport\Transport;
use CuyZ\WebZ\Soap\Client\HttpSoapSender;
use CuyZ\WebZ\Soap\Client\SoapSender;
use CuyZ\WebZ\Soap\Exception\SoapExtensionNotInstalledException;
use GuzzleHttp\Promise\PromiseInterface;

final class SoapTransport implements Transport, AsyncTransport
{
    private SoapSender $sender;

    public function __construct(?SoapSender $sender = null)
    {
        /**
         * The `extension_loaded` function must no be imported
         * or prefixed with a / so that the test works.
         * @see \CuyZ\WebZ\Soap\SoapTest
         */
        if (!extension_loaded('soap')) {
            throw new SoapExtensionNotInstalledException(); // @codeCoverageIgnore
        }

        if (null === $sender) {
            $this->sender = new HttpSoapSender();
        } else {
            $this->sender = $sender;
        }
    }

    public function sendAsync(object $payload, ?string $asyncCallHash): ?PromiseInterface
    {
        if ($payload instanceof SoapPayload) {
            return $this->sendRequest($payload, $asyncCallHash);
        }

        return null;
    }

    public function send(object $payload): ?RawResult
    {
        $result = null;

        if ($payload instanceof SoapPayload) {
            /** @var RawResult $result */
            $result = $this->sendRequest($payload)->wait();
        }

        return $result;
    }

    private function sendRequest(SoapPayload $payload, ?string $asyncCallHash = null): PromiseInterface
    {
        return $this->sender->send($payload, $asyncCallHash);
    }
}
