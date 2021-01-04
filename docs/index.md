# WebZ

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads][ico-downloads]][link-downloads]
[![Software License][ico-license]](LICENSE)
[![Master][ico-workflow]][link-workflow]

⚠️ This project is in the experimental phase. The API may change any time.

WebZ is a library that aims to abstract calls to different WebServices (in HTTP or SOAP).

It automatically handles caching, events and parsing results to an array.

```php
$bus = WebServiceBus::builder()
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

[ico-version]: https://img.shields.io/packagist/v/cuyz/webz.svg
[ico-downloads]: https://img.shields.io/packagist/dt/cuyz/webz.svg
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg
[ico-workflow]: https://github.com/CuyZ/WebZ/workflows/Tests/badge.svg?branch=master&event=push

[link-packagist]: https://packagist.org/packages/cuyz/webz
[link-downloads]: https://packagist.org/packages/cuyz/webz
[link-workflow]: https://github.com/CuyZ/WebZ/actions?query=workflow%3ATests
