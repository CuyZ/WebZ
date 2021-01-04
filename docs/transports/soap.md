# SOAP

To use the SOAP transport you need the `soap` PHP extension.

To install it see [SOAP Installation][link-soap].

## Configuration

SOAP requests are sent using Guzzle (to allow for async SOAP calls).

You can configure Guzzle client creation when instanciating the transport:

```php
use CuyZ\WebZ\Core\Bus\WebServiceBus;
use CuyZ\WebZ\Soap\SoapTransport;
use GuzzleHttp\Client;

$bus = WebServiceBus::builder()
    ->withTransport(SoapTransport::withFactory(fn() => new Client()));
```

## Payload

To use the transport in a webservice you need to implement the `payload` method
in you webservice class:

```php
use CuyZ\WebZ\Core\WebService;
use CuyZ\WebZ\Soap\SoapPayload;

final class MySoapWebService extends WebService
{
    protected function payload(): SoapPayload
    {
        return SoapPayload::forWsdl('https://my-example.com/api/wsdl.xml')
            ->withAction('getFoo');
    }
    
    // Other methods...
}
```

[link-soap]: https://www.php.net/manual/en/soap.setup.php
