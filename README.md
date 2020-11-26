# WebZ

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Software License][ico-license]](LICENSE)
[![Master][ico-workflow]][link-workflow]

:warning: This project is in the experimental phase. The API may change any time.

WebZ is a library that aims to abstract calls to different WebServices (in HTTP or SOAP).

It automatically handles caching, events and parsing results to an array.

```php
$bus = Bus::builder()
    ->withTransport(new HttpTransport())
    ->withTransport(new SoapTransport())
    ->withCache(new SomeCacheStore())
    ->withEventDispatcher(new SomeEventsDispatcher())
    ->build();

// Synchronous
$foo = $bus->call(new GetFooWebService(123));

// Asynchronous
$promises = $bus->callAsync(
    new GetFooWebService(123),
    new GetFooWebService(456),
);

foreach ($promises as $promise) {
    $foo = $promise->wait();
}
``` 

## Installation

```shell script
$ composer req cuyz/webz
```

### HTTP

The HTTP transport uses [Guzzle][link-guzzle] internally.

You can configure the Guzzle client creation when instantiating the transport:

```php
use CuyZ\WebZ\Core\Bus\WebServiceBus;
use CuyZ\WebZ\Http\HttpTransport;
use GuzzleHttp\Client;

$bus = WebServiceBus::builder()
    ->withTransport(new HttpTransport(fn() => new Client()));
```

### SOAP

To use the SOAP transport you need the `soap` PHP extension.

To install it see [SOAP Installation][link-soap].

SOAP requests are sent using Guzzle (to allow for async SOAP calls).

You can configure the Guzzle client creation when instanciating the transport:

```php
use CuyZ\WebZ\Core\Bus\WebServiceBus;
use CuyZ\WebZ\Soap\SoapTransport;
use GuzzleHttp\Client;

$bus = WebServiceBus::builder()
    ->withTransport(SoapTransport::withFactory(fn() => new Client()));
```

## Why?

Imagine you have to fetch some data from an external WebService.

You need to instanciate some client, make the call, parse the response, cache it,
handle exceptions, manually manage concurrent calls, log, etc.

All this code could be repeated in multiple places and projects:

```php
use GuzzleHttp\Client;

$client = new CLient();
$response = $client->request('GET', 'https://api.example.com/get-data');

$data = json_decode(
    $response->getBody()->getContents(),
    true,
    512,
    JSON_THROW_ON_ERROR
);

// Here you might have to handle the cache, exceptions, etc

echo $data['foo'];
```

## Exemple

With WebZ you can abstract all this. Let say we want to fetch this data object:

```php
namespace Acme;

class Place
{
    private string $name;
    
    public function __construct(string $name)
    {
        $this->name = $name;
    }
    
    public function name(): string
    {
        return $this->name;
    }
}
```

It can be fetched from an HTTP request:

```php
use Acme\Place;
use CuyZ\WebZ\Core\WebService;
use CuyZ\WebZ\Http\Payload\HttpPayload;

class GetPlace extends WebService
{
    private int $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    protected function payload(): HttpPayload
    {
        return HttpPayload::request(
            'GET',
            'https://my-api.com/v1/place/' . $this->id
        );
    }

    public function parse(array $data): Place
    {
        return new Place($data['name']);
    }
}
```

Or from SOAP:

```php
use Acme\Place;
use CuyZ\WebZ\Core\WebService;
use CuyZ\WebZ\Soap\SoapPayload;

class GetPlace extends WebService
{
    private int $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    protected function payload(): SoapPayload
    {
        return SoapPayload::forWsdl('https://my-example.com/api/wsdl.xml')
            ->withAction('getPlace')
            ->withArguments([$this->id]);
    }

    public function parse(array $data): Place
    {
        return new Place($data['name']);
    }
}
```

You then call any WebService via the Bus and a compatible transport will call the WebService:

```php
use Acme\Place;
use CuyZ\WebZ\Core\Bus\WebServiceBus;
use CuyZ\WebZ\Http\HttpTransport;
use CuyZ\WebZ\Soap\SoapTransport;

$bus = WebServiceBus::builder()
    ->withTransport(new HttpTransport())
    ->withTransport(new SoapTransport())
    ->build();

$place = $bus->call(new GetPlace(123));

echo $place->name();

// You can also call multiple webservices in concurrency
// provided that they use the same type of payload:
$promises = $bus->callAsync(
    new GetPlace(123),
    new GetPlace(456),
);

foreach ($promises as $promise) {
    /** @var Place $place */
    $place = $promise->wait();

    echo $place->name();
}
```

In the end the `GetPlace` class looks transport agnostic and can be reused anywhere
in your project.

And if the WebService changes anything (url, parameters, protocol, etc) you only
have one place to update in your code.

## Usage

### WebService

To implement a WebService all you need is a class extending `\CuyZ\WebZ\Core\WebService`.

```php
use CuyZ\WebZ\Core\WebService;

class GetPlace extends WebService
{
    protected function payload(): object
    {
        // The payload depends on the transport (SOAP or HTTP)
    }

    public function parse(array $data): object
    {
        // Here you have access to the result as an array
        // and can parse it to any object.
    }
}
```

### Cache

To use caching your WebService needs to implement `\CuyZ\WebZ\Core\Cache\WithCache`:

```php
use CuyZ\WebZ\Core\Cache\WithCache;
use CuyZ\WebZ\Core\WebService;

class GetPlace extends WebService implements WithCache
{
    public function cacheLifetime(): int
    {
        // TTL is in seconds
        // If the TTL is lower or equal to 0, the cache is disabled
        return 60;
    }

    protected function payload(): object { ... }
    
    public function parse(array $data): object { ... }
}
```

You also need to configure a cache store implementing PSR-16 (Simple Cache).

You can find one [on Packagist][link-psr-simple-cache].

For example with [cache/filesystem-adapter][link-cache-filesystem]:

```php
use CuyZ\WebZ\Core\Bus\WebServiceBus;
use CuyZ\WebZ\Http\HttpTransport;
use CuyZ\WebZ\Soap\SoapTransport;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Cache\Adapter\Filesystem\FilesystemCachePool;

$filesystemAdapter = new Local(__DIR__ . '/');
$filesystem = new Filesystem($filesystemAdapter);

$pool = new FilesystemCachePool($filesystem);

$bus = WebServiceBus::builder()
    ->withTransport(new HttpTransport())
    ->withTransport(new SoapTransport())
    ->withCache($pool)
    ->build();

$place = $bus->call(new GetPlace(123));

// This call comes from the cache
$place = $bus->call(new GetPlace(123));

echo $place->name();
```

### Events

You can subscribe to some events sent from the Bus.

It can be used to add logging for example.

You will need to install an event library that
implements PSR-14 (Event Dispatcher).

You can find one [on Packagist][link-psr-event-dispatcher].

For example with [symfony/event-dispatcher][link-symfony-event-dispatcher]:

```php
use CuyZ\WebZ\Core\Bus\WebServiceBus;
use CuyZ\WebZ\Core\Event\BeforeCallEvent;
use CuyZ\WebZ\Core\Event\FailedCallEvent;
use CuyZ\WebZ\Core\Event\SuccessfulCallEvent;
use CuyZ\WebZ\Http\HttpTransport;
use CuyZ\WebZ\Soap\SoapTransport;
use Symfony\Component\EventDispatcher\EventDispatcher;

$dispatcher = new EventDispatcher();

$bus = WebServiceBus::builder()
    ->withTransport(new HttpTransport())
    ->withTransport(new SoapTransport())
    ->withEventDispatcher($dispatcher)
    ->build();

$dispatcher->addListener(
    BeforeCallEvent::class,
    function (BeforeCallEvent $event) {
        // This event is dispatched before a WebService is called
    }
);

$dispatcher->addListener(
    FailedCallEvent::class,
    function (FailedCallEvent $event) {
        // This event is dispatched when the call failed
    }
);

$dispatcher->addListener(
    SuccessfulCallEvent::class,
    function (SuccessfulCallEvent $event) {
        // This event is dispatched when the call had no error
    }
);

$place = $bus->call(new GetPlace(123));

echo $place->name();
```

### Exceptions

By default Exception are thrown as-is by the Bus.

You can handle exceptions in any WebService by implementing `\CuyZ\WebZ\Core\Exception\HandlesExceptions`:

```php
use CuyZ\WebZ\Core\Exception\HandlesExceptions;
use CuyZ\WebZ\Core\WebService;

class GetPlace extends WebService implements HandlesExceptions
{
    public function onException(Exception $e): void
    {
        throw new MyCustomException($e);
    }

    protected function payload(): object { ... }
    
    public function parse(array $data): object { ... }
}
```

### Transport

WebZ comes with an HTTP and a SOAP transports — you may add your own if needed.

A transport is a class that implements `\CuyZ\WebZ\Core\Transport\Transport`.

The `send` method accepts an `object` payload that you need to test for compatibility.

```php
use CuyZ\WebZ\Core\Result\RawResult;
use CuyZ\WebZ\Core\Transport\Transport;

class MyTransport implements Transport
{
    public function send(object $payload): ?RawResult
    {
        // If the payload is not supported by this transport
        // it must return null
        if (!$payload instanceof MyPayload) {
            return null;
        }

        try {
            $raw = $this->someService->call($payload->someMethod(...));

            // For a successful call
            // $raw must be an array
            return RawResult::ok($raw);
        } catch (\Exception $e) {
            // For a failed call
            return RawResult::err($e);
        }
    }
}
```

You then need to register it in the Bus:

```php
$bus = Bus::builder()
    // The order is important, transports are called in order
    // and the first compatible one is called.
    ->withTransport(new MyTransport())
    ->withTransport( ... )
    ->build();

$foo = $bus->call(new GetFooWebService(123));
``` 

### Async transport

A transport can implement the `\CuyZ\WebZ\Core\Transport\AsyncTransport` interface
to send payloads asynchronously.

```php
use CuyZ\WebZ\Core\Result\RawResult;
use CuyZ\WebZ\Core\Transport\AsyncTransport;
use GuzzleHttp\Promise\PromiseInterface;

class MyTransport implements AsyncTransport
{
    public function send(object $payload): ?RawResult
    {
        return $this->sendAsync($payload, null)->wait();
    }

    public function sendAsync(object $payload, ?string $asyncCallHash): ?PromiseInterface
    {
        // If the payload is not supported by this transport
        // it must return null
        if (!$payload instanceof MyPayload) {
            return null;
        }

        return $this->someService
            ->call($payload->someMethod(...))
            // The promise must return an instance of RawResult
            ->then(fn(SomResponse $res) => RawResult::ok($res->toArray()));
    }
}
```

### Contributing

To setup the dev you need to clone the repo and then run:

```shell script
$ make install
```

To run the full test suite:

```shell script
$ make test
```

To run only unit tests:

```shell script
$ make test-unit
```

[ico-version]: https://img.shields.io/packagist/v/cuyz/webz.svg
[ico-downloads]: https://img.shields.io/packagist/dt/cuyz/webz.svg
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg
[ico-workflow]: https://github.com/CuyZ/WebZ/workflows/Tests/badge.svg?branch=master&event=push

[link-packagist]: https://packagist.org/packages/cuyz/webz
[link-downloads]: https://packagist.org/packages/cuyz/webz
[link-workflow]: https://github.com/CuyZ/WebZ/actions?query=workflow%3ATests
[link-guzzle]: https://github.com/guzzle/guzzle
[link-soap]: https://www.php.net/manual/en/soap.setup.php
[link-psr-simple-cache]: https://packagist.org/providers/psr/simple-cache-implementation
[link-psr-event-dispatcher]: https://packagist.org/providers/psr/event-dispatcher-implementation
[link-symfony-event-dispatcher]: https://symfony.com/doc/current/components/event_dispatcher.html
[link-cache-filesystem]: https://github.com/php-cache/filesystem-adapter
