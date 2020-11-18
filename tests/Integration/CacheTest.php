<?php

use Cache\Adapter\PHPArray\ArrayCachePool;
use Cache\Adapter\Void\VoidCachePool;
use CuyZ\WebZ\Core\Bus\Bus;
use CuyZ\WebZ\Core\Transport\Transport;
use CuyZ\WebZ\Core\WebService;
use CuyZ\WebZ\Http\HttpTransport;
use CuyZ\WebZ\Soap\SoapTransport;
use CuyZ\WebZ\Tests\Fixture\Soap\Client\IntegrationTestSoapClient;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyCacheWebService;
use GuzzleHttp\Client;

dataset('cache', [
    [
        'transport' => new SoapTransport(IntegrationTestSoapClient::factory()),
        'webservice' => DummyCacheWebService::soap('foo', 10),
    ],
    [
        'transport' => new HttpTransport(fn() => new Client()),
        'webservice' => DummyCacheWebService::httpSingle('bar', 10),
    ],
    [
        'transport' => new HttpTransport(fn() => new Client()),
        'webservice' => DummyCacheWebService::httpMultiplex('bar', 10),
    ],
]);

it('returns a memoized result', function (Transport $transport, WebService $webService) {
    $bus = Bus::builder()
        ->withTransport($transport)
        ->withCache(new VoidCachePool())
        ->build();

    $result1 = $bus->call($webService);
    $result2 = $bus->call($webService);

    expect($result1)->toEqual($result2);
})->with('cache');

it('returns a new result for each call', function (Transport $transport, WebService $webService) {
    $bus = Bus::builder()
        ->withTransport($transport)
        ->withCache(new VoidCachePool())
        ->withoutMemoization()
        ->build();

    $result1 = $bus->call($webService);
    $result2 = $bus->call($webService);

    expect($result1)->not->toEqual($result2);
})->with('cache');

it('returns a cached result', function (Transport $transport, WebService $webService) {
    $pool = new ArrayCachePool();

    $bus = Bus::builder()
        ->withTransport($transport)
        ->withCache($pool)
        ->build();

    $result1 = $bus->call($webService);

    expect($pool->has($webService->getPayloadHash()))->toBeTrue();

    $result2 = $bus->call($webService);

    expect($result1)->toEqual($result2);
})->with('cache');
