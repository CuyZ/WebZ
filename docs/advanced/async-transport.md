# Async transport

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
