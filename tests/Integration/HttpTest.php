<?php

use CuyZ\WebZ\Core\Bus\Bus;
use CuyZ\WebZ\Http\ClientFactory;
use CuyZ\WebZ\Http\HttpTransport;
use CuyZ\WebZ\Http\Payload\HttpPayload;
use CuyZ\WebZ\Http\Payload\RequestPayload;
use CuyZ\WebZ\Http\Transformer\JsonTransformer;
use CuyZ\WebZ\Http\Transformer\ScalarTransformer;
use CuyZ\WebZ\Tests\Fixture\FakeResult;
use CuyZ\WebZ\Tests\Fixture\Server\HttpHandler;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyWrapResultWebService;
use GuzzleHttp\Client;

it('returns a parsed result', function ($factory, RequestPayload $payload, $raw) {
    $bus = Bus::builder()
        ->withTransport(new HttpTransport($factory))
        ->build();

    $webService = new DummyWrapResultWebService($payload);

    /** @var FakeResult $result */
    $result = $bus->call($webService);

    expect($result)->toBeInstanceOf(FakeResult::class);
    expect($result->raw)->toBe($raw);
})->with(function () {
    $factories = [
        fn() => new Client(),
        new class implements ClientFactory {
            public function build(): Client
            {
                return new Client();
            }
        },
        null
    ];

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

    foreach ($factories as $factory) {
        foreach ($sets as $set) {
            yield ['factory' => $factory] + $set;
        }
    }
});
