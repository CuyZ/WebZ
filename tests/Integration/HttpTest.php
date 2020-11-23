<?php

use CuyZ\WebZ\Core\Bus\Bus;
use CuyZ\WebZ\Core\Guzzle\GuzzleClientFactory;
use CuyZ\WebZ\Http\HttpTransport;
use CuyZ\WebZ\Http\Payload\HttpPayload;
use CuyZ\WebZ\Http\Transformer\JsonTransformer;
use CuyZ\WebZ\Http\Transformer\ScalarTransformer;
use CuyZ\WebZ\Tests\Fixture\FakeResult;
use CuyZ\WebZ\Tests\Fixture\Server\HttpHandler;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyWrapResultWebService;
use GuzzleHttp\Client;

$payload = fn() => HttpPayload::request('POST', HttpHandler::route('returnValue'));

$sets = [
    [
        'payload' => $payload()->withBody('foo')
            ->withHeader('Content-Type', 'text/plain')
            ->withTransformer(new ScalarTransformer()),
        'result' => ['value' => 'foo'],
    ],
    [
        'payload' => $payload()->withBody(null)
            ->withHeader('Content-Type', 'text/plain')
            ->withTransformer(new ScalarTransformer()),
        'result' => ['value' => ''],
    ],
    [
        'payload' => $payload()->withJson(['foo' => 'bar'])
            ->withTransformer(new JsonTransformer()),
        'result' => ['foo' => 'bar'],
    ],
];

it('returns a parsed result', function ($factory, HttpPayload $payload, $raw) {
    $bus = Bus::builder()
        ->withTransport(new HttpTransport($factory))
        ->build();

    $webService = new DummyWrapResultWebService($payload);

    /** @var FakeResult $result */
    $result = $bus->call($webService);

    expect($result)->toBeInstanceOf(FakeResult::class);
    expect($result->raw)->toBe($raw);
})->with(function () use ($sets) {
    $factories = [
        fn() => new Client(),
        new class implements GuzzleClientFactory {
            public function build(?string $payloadGroupHash): Client
            {
                return new Client();
            }
        },
        null
    ];

    foreach ($factories as $factory) {
        foreach ($sets as $set) {
            yield ['factory' => $factory] + $set;
        }
    }
});

it('does an async call', function () use ($sets) {
    $bus = Bus::builder()
        ->withTransport(new HttpTransport())
        ->build();

    $data = array_map(
        function (array $set) {
            return [
                'webservice' => new DummyWrapResultWebService($set['payload']),
                'result' => $set['result'],
            ];
        },
        $sets
    );

    $promises = $bus->callAsync(
        ...array_map(fn(array $set) => $set['webservice'], $data)
    );

    foreach ($promises as $index => $promise) {
        /** @var FakeResult $result */
        $result = $promise->wait();

        expect($result)->toBeInstanceOf(FakeResult::class);
        expect($result->raw)->toBe($data[$index]['result']);
    }
});

it('contains the request and response traces', function () {
    $payload = HttpPayload::request()
        ->withMethod('POST')
        ->withBaseUri(HttpHandler::URI)
        ->withHeader('X-Foo', 'bar')
        ->withHeader('User-Agent', 'cuyz/webz')
        ->withAuthBearer('abcd1234')
        ->withQuery('route', 'returnValue')
        ->withQuery('a', 'b')
        ->withJson(['foo' => 'bar'])
        ->withTransformer(new JsonTransformer());

    $transport = new HttpTransport();

    $result = $transport->send($payload);

    expect($result->requestTrace())->toBe(<<<REQUEST
POST /http?route=returnValue&a=b HTTP/1.1
Content-Length: 13
Content-Type: application/json
Host: localhost:8080
X-Foo: bar
User-Agent: cuyz/webz
Authorization: Bearer abcd1234

{"foo":"bar"}
REQUEST
);

    $date = (new DateTime('now', new DateTimeZone('GMT')))->format('r');
    $date = str_replace('+0000', 'GMT', $date);

    expect($result->responseTrace())->toBe(<<<RESPONSE
HTTP/1.1 200 OK
Content-Type: application/json
Server: ReactPHP/1
Date: $date
Content-Length: 13
Connection: close

{"foo":"bar"}
RESPONSE
);
});
