<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Http;

use Closure;
use CuyZ\WebZ\Core\Result\RawResult;
use CuyZ\WebZ\Core\Transport\Transport;
use CuyZ\WebZ\Http\Exception\EmptyMultiplexPayloadException;
use CuyZ\WebZ\Http\Exception\HttpClientNotInstalledException;
use CuyZ\WebZ\Http\Payload\HttpPayload;
use CuyZ\WebZ\Http\Payload\MultiplexPayload;
use CuyZ\WebZ\Http\Payload\RequestPayload;
use CuyZ\WebZ\Http\Transformer\AutoTransformer;
use CuyZ\WebZ\Http\Transformer\Transformer;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @psalm-type HttpFactory = Closure():HttpClientInterface
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
     * @throws HttpClientNotInstalledException
     */
    public function __construct($factory = null)
    {
        /**
         * The `class_exists` function must no be imported
         * or prefixed with a / so that the unit test works.
         * @see tests/Platform/HttpTest.php
         */
        if (!class_exists(HttpClient::class)) {
            throw new HttpClientNotInstalledException(); // @codeCoverageIgnore
        }

        if ($factory instanceof ClientFactory) {
            $factory = fn(): HttpClientInterface => $factory->build();
        }

        if (!$factory instanceof Closure) {
            $factory = fn(): HttpClientInterface => HttpClient::create();
        }

        $this->factory = $factory;
    }

    /**
     * @param HttpPayload|object $payload
     * @return RawResult
     * @throws TransportExceptionInterface
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

    private function makeClient(): HttpClientInterface
    {
        return ($this->factory)();
    }

    /**
     * @param RequestPayload $payload
     * @return RawResult
     * @throws TransportExceptionInterface
     */
    private function sendSingleRequest(RequestPayload $payload): RawResult
    {
        $client = $this->makeClient();

        $raw = $client->request($payload->method(), $payload->url(), $payload->options());
        $raw = ($payload->transformer() ?? new AutoTransformer())->toArray($raw);

        return RawResult::ok($raw);
    }

    /**
     * @param MultiplexPayload $payload
     * @return RawResult
     * @throws TransportExceptionInterface
     */
    private function sendMultiplexRequests(MultiplexPayload $payload): RawResult
    {
        if (count($payload->requests()) === 0) {
            throw new EmptyMultiplexPayloadException();
        }

        $this->prepareRequests($payload);

        $client = $this->makeClient();

        $responses = [];

        /** @var Transformer[] $transformers */
        $transformers = [];

        foreach ($payload->requests() as $request) {
            $response = $client->request($request->method(), $request->url(), $request->options());

            $transformers[] = $payload->transformer() ?? $request->transformer() ?? new AutoTransformer();
            $responses[] = $response;
        }

        $raw = $client->stream($responses, $payload->streamTimeout());

        $responses = [];

        while ($raw->valid()) {
            if ($raw->current()->isLast()) {
                /** @var array $data */
                $data = $raw->key()->getInfo('user_data');

                $index = (int)$data['index'];

                $responses[$index] = $transformers[$index]->toArray($raw->key());
            }

            $raw->next();
        }

        return RawResult::ok($responses);
    }

    private function prepareRequests(MultiplexPayload $payload): void
    {
        foreach ($payload->requests() as $index => $request) {
            $options = $request->options();

            $options['user_data'] = ['index' => $index];

            $request->withOptions($options);
        }
    }
}
