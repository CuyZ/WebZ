<?php
/** @noinspection PhpComposerExtensionStubsInspection */
declare(strict_types=1);

namespace CuyZ\WebZ\Soap;

use Closure;
use CuyZ\WebZ\Core\Guzzle\GuzzleClientFactory;
use CuyZ\WebZ\Core\Result\RawResult;
use CuyZ\WebZ\Core\Transport\AsyncTransport;
use CuyZ\WebZ\Core\Transport\Transport;
use CuyZ\WebZ\Soap\Client\GuzzleSoapSender;
use CuyZ\WebZ\Soap\Client\SoapSender;
use CuyZ\WebZ\Soap\Exception\SoapExtensionNotInstalledException;
use GuzzleHttp\Promise\PromiseInterface;

final class SoapTransport implements Transport, AsyncTransport
{
    private SoapSender $sender;

    /**
     * @param GuzzleClientFactory|Closure|null $factory
     * @return SoapTransport
     */
    public static function withFactory($factory = null): SoapTransport
    {
        return new SoapTransport(new GuzzleSoapSender($factory));
    }

    public function __construct(?SoapSender $sender = null)
    {
        /**
         * The `extension_loaded` function must no be imported
         * or prefixed with a / so that the unit test works.
         * @see tests/Platform/SoapTest.php
         */
        if (!extension_loaded('soap')) {
            throw new SoapExtensionNotInstalledException(); // @codeCoverageIgnore
        }

        if (null === $sender) {
            $this->sender = new GuzzleSoapSender();
        } else {
            $this->sender = $sender;
        }
    }

    public function sendAsync(object $payload, ?string $payloadGroupHash): ?PromiseInterface
    {
        if ($payload instanceof SoapPayload) {
            return $this->sendRequest($payload, $payloadGroupHash);
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

    private function sendRequest(SoapPayload $payload, ?string $payloadGroupHash = null): PromiseInterface
    {
        return $this->sender->send($payload);
    }
}
