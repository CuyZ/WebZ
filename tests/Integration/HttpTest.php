<?php

use CuyZ\WebZ\Core\Bus\Bus;
use CuyZ\WebZ\Http\ClientFactory;
use CuyZ\WebZ\Http\HttpTransport;
use CuyZ\WebZ\Http\Payload\HttpPayload;
use CuyZ\WebZ\Http\Transformer\AutoTransformer;
use CuyZ\WebZ\Http\Transformer\ScalarTransformer;
use CuyZ\WebZ\Http\Transformer\Transformer;
use CuyZ\WebZ\Tests\Fixture\FakeResult;
use CuyZ\WebZ\Tests\Fixture\Server\HttpHandler;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyWrapResultWebService;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

it('returns a parsed result', function ($factory, array $options, Transformer $transformer, $raw) {
    $bus = Bus::builder()
        ->withTransport(new HttpTransport($factory))
        ->build();

    $payload = HttpPayload::request('POST', HttpHandler::route('returnValue'))
        ->withOptions($options)
        ->withTransformer($transformer);

    $webService = new DummyWrapResultWebService($payload);

    /** @var FakeResult $result */
    $result = $bus->call($webService);

    expect($result)->toBeInstanceOf(FakeResult::class);
    expect($result->raw)->toBe($raw);
})->with(function () {
    $factories = [
        fn() => HttpClient::create(),
        new class implements ClientFactory
        {
            public function build(): HttpClientInterface
            {
                return HttpClient::create();
            }
        },
        null
    ];

    $sets = [
        [
            'options' => ['body' => 'foo', 'headers' => ['Content-Type' => 'text/plain']],
            'transformer' => new ScalarTransformer(),
            'result' => ['value' => 'foo'],
        ],
        [
            'options' => ['body' => null, 'headers' => ['Content-Type' => 'text/plain']],
            'transformer' => new ScalarTransformer(),
            'result' => ['value' => ''],
        ],
        [
            'options' => ['json' => ['foo' => 'bar']],
            'transformer' => new AutoTransformer(),
            'result' => ['foo' => 'bar'],
        ],
    ];

    foreach ($factories as $factory) {
        foreach ($sets as $set) {
            yield ['factory' => $factory] + $set;
        }
    }
});
