<?php

use CuyZ\WebZ\Http\ClientFactory;
use CuyZ\WebZ\Http\Exception\EmptyMultiplexPayloadException;
use CuyZ\WebZ\Http\HttpTransport;
use CuyZ\WebZ\Http\Payload\HttpPayload;
use CuyZ\WebZ\Http\Payload\RequestPayload;
use CuyZ\WebZ\Http\Transformer\ScalarTransformer;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Utils;
use Tests\Mocks;

it('returns null for an incompatible payload', function ($clientFactory) {
    $transport = new HttpTransport($clientFactory);

    expect($transport->send(new stdClass()))->toBeNull();
})->with([
    null,

    fn() => new Client(),

    new class implements ClientFactory {
        public function build(): Client
        {
            return new Client();
        }
    },
]);

it('returns a psr http response', function () {
    $payload = HttpPayload::request('GET', 'https://localhost')
        ->withTransformer(new ScalarTransformer());

    $client = Mocks::httpClient(new Response(200, [], 'foo'));

    $transport = new HttpTransport(fn() => $client);

    $result = $transport->send($payload);

    expect($result->data())->toBe(['value' => 'foo']);
});

it('throws an exception on errors', function () {
    $payload = HttpPayload::request('GET', 'https://localhost');

    $client = Mocks::httpClient(new RequestException('some error', new Request('GET', 'test')));

    $transport = new HttpTransport(fn() => $client);
    $transport->send($payload);
})->throws(RequestException::class);

it('throws on empty multiplex payload', function () {
    $transport = new HttpTransport(fn() => Mocks::httpClient());

    $transport->send(HttpPayload::multiplex());
})->throws(EmptyMultiplexPayloadException::class);

it('returns a streamed response', function () {
    $payload = HttpPayload::multiplex()
        ->with(RequestPayload::request('GET', 'https://localhost/1')->withTransformer(new ScalarTransformer()))
        ->with(RequestPayload::request('GET', 'https://localhost/2')->withTransformer(new ScalarTransformer()));

    $client = Mocks::httpClient(
        new Response(200, [], Utils::streamFor('Hello World')),
        new Response(200, [], Utils::streamFor('Hello John')),
    );

    $transport = new HttpTransport(fn() => $client);

    $result = $transport->send($payload);

    expect($result->data())->toBe([
        ['value' => 'Hello World'],
        ['value' => 'Hello John'],
    ]);
});
