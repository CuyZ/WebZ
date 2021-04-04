<?php
/** @noinspection PhpComposerExtensionStubsInspection */
declare(strict_types=1);

namespace CuyZ\WebZ\Soap\Client;

use Closure;
use CuyZ\WebZ\Core\Http\AutoFactory;
use CuyZ\WebZ\Core\Http\HttpClientFactory;
use CuyZ\WebZ\Core\Result\RawResult;
use CuyZ\WebZ\Core\Support\Arr;
use CuyZ\WebZ\Http\Formatter\HttpMessageFormatter;
use CuyZ\WebZ\Soap\Psr\SoapToPsrConverter;
use CuyZ\WebZ\Soap\SoapPayload;
use Exception;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;
use SoapFault;

final class GuzzleSoapSender implements SoapSender
{
    private HttpClientFactory $factory;
    private HttpMessageFormatter $formatter;

    /**
     * @param HttpClientFactory|Closure|null $factory
     */
    public function __construct($factory = null)
    {
        $this->factory = new AutoFactory($factory);
        $this->formatter = new HttpMessageFormatter();
    }

    public function send(SoapPayload $payload, ?string $asyncCallHash = null): PromiseInterface
    {
        $converter = new SoapToPsrConverter($payload);
        $client = $this->factory->build($asyncCallHash);

        $request = $converter->toRequest();
        $promise = $client->sendAsync($request);

        return $promise->then(
            function (ResponseInterface $response) use ($request, $converter): RawResult {
                $soapFault = null;

                try {
                    $raw = $converter->fromResponse($response);
                } catch (SoapFault $soapFault) {
                    $raw = $soapFault;
                }

                if ($raw instanceof SoapFault) {
                    $soapFault = $raw;
                    $raw = $raw->getMessage();
                }

                $raw = $this->rawToArray($raw);

                if ($soapFault instanceof SoapFault) {
                    $result = RawResult::err($soapFault, $raw);
                } else {
                    $result = RawResult::ok($raw);
                }

                return $result
                    ->withRequestTrace($this->formatter->formatRequest($request))
                    ->withResponseTrace($this->formatter->formatResponse($response));
            },
            function (Exception $e): RawResult {
                return RawResult::err($e, $this->rawToArray($e->getMessage()));
            }
        );
    }

    /**
     * @param mixed $raw
     * @return array
     */
    private function rawToArray($raw): array
    {
        if (!is_array($raw) && !is_object($raw)) {
            $raw = ['value' => $raw];
        }

        return Arr::castToArray($raw);
    }
}
