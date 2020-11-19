<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Http;

use Closure;
use CuyZ\WebZ\Core\Result\RawResult;
use CuyZ\WebZ\Core\Transport\Transport;
use CuyZ\WebZ\Http\Exception\EmptyMultiplexPayloadException;
use CuyZ\WebZ\Http\Payload\HttpPayload;
use CuyZ\WebZ\Http\Payload\MultiplexPayload;
use CuyZ\WebZ\Http\Payload\RequestPayload;
use CuyZ\WebZ\Http\Transformer\JsonTransformer;
use CuyZ\WebZ\Http\Transformer\Transformer;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @psalm-type HttpFactory = Closure():Client
 */
final class HttpTransport implements Transport
{
    /**
     * @phpstan-var Closure
     * @psalm-var HttpFactory
     */
    private $factory;

    /**
     * @param ClientFactory|Closure|null $factory
     */
    public function __construct($factory = null)
    {
        if ($factory instanceof ClientFactory) {
            $factory = fn(): Client => $factory->build();
        }

        if (!$factory instanceof Closure) {
            $factory = fn(): Client => new Client();
        }

        $this->factory = $factory;
    }

    /**
     * @param HttpPayload|object $payload
     * @return RawResult
     */
    public function send(object $payload): ?RawResult
    {
        if ($payload instanceof RequestPayload) {
            return $this->sendSingleRequest($payload);
        }

        if ($payload instanceof MultiplexPayload) {
            return $this->sendMultiplexRequests($payload);
        }

        return null;
    }

    /**
     * @param RequestPayload $payload
     * @return RawResult
     */
    private function sendSingleRequest(RequestPayload $payload): RawResult
    {
        $client = $this->makeClient();

        $raw = $client->request($payload->method(), $payload->url(), $payload->options());
        $raw = ($payload->transformer() ?? new JsonTransformer())->toArray($raw);

        return RawResult::ok($raw);
    }

    private function sendMultiplexRequests(MultiplexPayload $payload): RawResult
    {
        if (count($payload->requests()) === 0) {
            throw new EmptyMultiplexPayloadException();
        }

        $client = $this->makeClient();

        /** @var Transformer[] $transformers */
        $transformers = [];

        /** @var PromiseInterface[] $promises */
        $promises = [];

        foreach ($payload->requests() as $index => $request) {
            $promises[$index] = $client->requestAsync($request->method(), $request->url(), $request->options());

            $transformers[$index] = $payload->transformer() ?? $request->transformer() ?? new JsonTransformer();
        }

        /** @var ResponseInterface[] $responses */
        $responses = [];

        foreach ($promises as $key => $promise) {
            $responses[$key] = $promise->wait();
        }

        $raw = [];

        foreach ($responses as $index => $response) {
            $raw[$index] = $transformers[$index]->toArray($response);
        }

        return RawResult::ok($raw);
    }

    private function makeClient(): Client
    {
        return ($this->factory)();
    }
}
