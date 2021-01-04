# HTTP

The HTTP transport uses [Guzzle][link-guzzle] internally.

## Configuration

You can configure the Guzzle client creation when instantiating the transport:

```php
use CuyZ\WebZ\Core\Bus\WebServiceBus;
use CuyZ\WebZ\Http\HttpTransport;
use GuzzleHttp\Client;

$bus = WebServiceBus::builder()
    ->withTransport(new HttpTransport(fn() => new Client()));
```

## Payload

To use the transport in a webservice you need to implement the `payload` method
in you webservice class:

```php
use CuyZ\WebZ\Core\WebService;
use CuyZ\WebZ\Http\Payload\HttpPayload;

final class MyHttpWebService extends WebService
{
    protected function payload(): HttpPayload
    {
        return HttpPayload::request('GET', 'https://my-api.com/v1/foo');
    }
    
    // Other methods...
}
```

[link-guzzle]: https://github.com/guzzle/guzzle
