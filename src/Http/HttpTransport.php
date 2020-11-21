<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Http;

use Closure;
use CuyZ\WebZ\Core\Result\RawResult;
use CuyZ\WebZ\Core\Transport\AsyncTransport;
use CuyZ\WebZ\Core\Transport\Transport;
use CuyZ\WebZ\Http\Formatter\HttpMessageFormatter;
use CuyZ\WebZ\Http\Payload\HttpPayload;
use CuyZ\WebZ\Http\Transformer\JsonTransformer;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @psalm-type HttpFactory = Closure():Client
 */
final class HttpTransport implements Transport, AsyncTransport
{
    /**
     * @phpstan-var Closure
     * @psalm-var HttpFactory
     */
    private $factory;

    /** @var Client[] */
    private array $clients = [];

    private HttpMessageFormatter $formatter;

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
        $this->formatter = new HttpMessageFormatter();
    }

    /**
     * @param HttpPayload|object $payload
     * @return RawResult
     */
    public function send(object $payload): ?RawResult
    {
        $raw = null;

        if ($payload instanceof HttpPayload) {
            /** @var RawResult $raw */
            $raw = $this->sendRequest($payload, $this->makeClient())->wait();
        }

        return $raw;
    }

    public function sendAsync(object $payload, ?string $payloadGroupHash): ?PromiseInterface
    {
        if ($payload instanceof HttpPayload) {
            return $this->sendRequest($payload, $this->makeClient($payloadGroupHash));
        }

        return null;
    }

    private function sendRequest(HttpPayload $payload, Client $client): PromiseInterface
    {
        /**
         * @psalm-suppress DeprecatedMethod
         * @var array $config
         */
        $config = $client->getConfig();

        /** @var HandlerStack|callable $handler */
        $handler = $config['handler'] ?? HandlerStack::create();

        if (!$handler instanceof HandlerStack) {
            /** @psalm-suppress MixedArgumentTypeCoercion */
            $handler = HandlerStack::create($handler);
        }

        $request = null;

        /** @psalm-suppress MixedArgumentTypeCoercion */
        $handler->push(Middleware::tap(
            function (RequestInterface $req) use (&$request): void {
                $request = $req;
            }
        ));

        $config['handler'] = $handler;

        $client = new Client($config);

        $promise = $client->requestAsync(
            $payload->method(),
            $payload->url(),
            $payload->options()
        );

        return $promise
            ->then(
                function (ResponseInterface $response) use ($request, $payload): RawResult {
                    $data = ($payload->transformer() ?? new JsonTransformer())->toArray($response);

                    $raw = RawResult::ok($data)
                        ->withResponseTrace($this->formatter->formatResponse($response));

                    if ($request instanceof RequestInterface) {
                        return $raw->withRequestTrace($this->formatter->formatRequest($request));
                    }

                    return $raw;
                }
            );
    }

    private function makeClient(?string $groupId = null): Client
    {
        if (null === $groupId) {
            return ($this->factory)();
        }

        return $this->clients[$groupId] ??= $this->makeClient();
    }
}
