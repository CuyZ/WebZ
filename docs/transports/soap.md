# SOAP

To use the SOAP transport you need the `soap` PHP extension.

To install it see [SOAP Installation][link-soap].

## Activation

The transport needs to be activated using the builder:

```php
use CuyZ\WebZ\Core\Bus\WebServiceBus;
use CuyZ\WebZ\Soap\SoapTransport;
use GuzzleHttp\Client;

$bus = WebServiceBus::builder()
    ->withTransport(new SoapTransport());
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
