<?php

use CuyZ\WebZ\Http\ClientFactory;
use CuyZ\WebZ\Http\Exception\EmptyMultiplexPayloadException;
use CuyZ\WebZ\Http\HttpTransport;
use CuyZ\WebZ\Http\Payload\HttpPayload;
use CuyZ\WebZ\Http\Transformer\ScalarTransformer;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

it('returns null for an incompatible payload', function ($clientFactory) {
    $transport = new HttpTransport($clientFactory);

    expect($transport->send(new stdClass()))->toBeNull();
})->with([
    null,

    fn() => new MockHttpClient(),

    new class implements ClientFactory {
        public function build(): HttpClientInterface
        {
            return new MockHttpClient();
        }
    },
]);

it('returns a symfony http client response', function () {
    $payload = HttpPayload::request('GET', 'https://localhost')
        ->withTransformer(new ScalarTransformer());

    $responses = [
        new MockResponse('foo')
    ];

    $transport = new HttpTransport(fn() => new MockHttpClient($responses));

    $result = $transport->send($payload);

    expect($result->data())->toBe(['value' => 'foo']);
});

it('throws an exception on errors', function () {
    $payload = HttpPayload::request('GET', 'https://localhost');

    $body = function () {
        /**
         * Empty strings are turned into timeouts
         * @see https://symfony.com/doc/current/http_client.html#testing-http-clients-and-responses
         */
        yield '';
    };

    $responses = [
        new MockResponse($body())
    ];

    $transport = new HttpTransport(fn() => new MockHttpClient($responses));
    $transport->send($payload);
})->throws(TransportExceptionInterface::class);

it('throws on empty multiplex payload', function () {
    $transport = new HttpTransport(fn() => new MockHttpClient());

    $transport->send(HttpPayload::multiplex());
})->throws(EmptyMultiplexPayloadException::class);

it('returns a streamed response', function () {
    $payload = HttpPayload::multiplex()
        ->with('GET', 'https://localhost/1', [], new ScalarTransformer())
        ->with('GET', 'https://localhost/2', [], new ScalarTransformer());

    $responses = [
        new MockResponse((function () {
            yield 'Hello';
            yield 'World';
        })()),
        new MockResponse((function () {
            yield 'Hello';
            yield 'John';
        })()),
    ];

    $transport = new HttpTransport(fn() => new MockHttpClient($responses));

    $result = $transport->send($payload);

    expect($result->data())->toBe([
        ['value' => 'HelloWorld'],
        ['value' => 'HelloJohn'],
    ]);
});
