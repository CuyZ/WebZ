<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Http;

use Closure;
use CuyZ\WebZ\Core\Guzzle\AutoFactory;
use CuyZ\WebZ\Core\Guzzle\GuzzleClientFactory;
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

final class HttpTransport implements Transport, AsyncTransport
{
    private GuzzleClientFactory $factory;
    private HttpMessageFormatter $formatter;

    /**
     * @param GuzzleClientFactory|Closure|null $factory
     */
    public function __construct($factory = null)
    {
        $this->factory = new AutoFactory($factory);
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
            $raw = $this->sendRequest($payload, $this->factory->build(null))->wait();
        }

        return $raw;
    }

    public function sendAsync(object $payload, ?string $asyncCallHash): ?PromiseInterface
    {
        if ($payload instanceof HttpPayload) {
            return $this->sendRequest($payload, $this->factory->build($asyncCallHash));
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
}
