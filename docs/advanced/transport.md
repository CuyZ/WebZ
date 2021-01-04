# Transport

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
$bus = WebServiceBus::builder()
    // The order is important, transports are tried in order
    // and the first compatible one is called.
    ->withTransport(new MyTransport())
    ->withTransport( ... )
    ->build();

$foo = $bus->call(new GetFooWebService(123));
``` 
