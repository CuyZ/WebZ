<?php

use CuyZ\WebZ\Core\Guzzle\GuzzleClientFactory;
use CuyZ\WebZ\Http\HttpTransport;
use CuyZ\WebZ\Http\Payload\HttpPayload;
use CuyZ\WebZ\Http\Transformer\ScalarTransformer;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Tests\Mocks;

it('returns null for an incompatible payload', function ($clientFactory) {
    $transport = new HttpTransport($clientFactory);

    expect($transport->send(new stdClass()))->toBeNull();
    expect($transport->sendAsync(new stdClass(), null))->toBeNull();
})->with([
    null,

    fn() => new Client(),

    new class implements GuzzleClientFactory {
        public function build(?string $payloadGroupHash): Client
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
