<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Tests\Fixture\Soap;

use CuyZ\WebZ\Core\Result\RawResult;
use CuyZ\WebZ\Core\Support\Arr;
use CuyZ\WebZ\Soap\Client\SoapSender;
use CuyZ\WebZ\Soap\SoapPayload;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use SoapFault;

final class FakeSoapSender implements SoapSender
{
    private array $responses;

    public function __construct(array $responses = [])
    {
        $this->responses = $responses;
    }

    public function send(SoapPayload $payload, ?string $asyncCallHash = null): PromiseInterface
    {
        $response = $this->responses[$payload->action()] ?? null;
        $raw = $response;

        if ($response instanceof SoapFault) {
            $raw = $response->getMessage();
        }

        if (!is_array($raw) && !is_object($raw)) {
            $raw = ['value' => $raw];
        }

        $raw = Arr::castToArray($raw);

        if ($response instanceof SoapFault) {
            $result = RawResult::err($response, $raw);
        } else {
            $result = RawResult::ok($raw);
        }

        return new FulfilledPromise($result);
    }
}
