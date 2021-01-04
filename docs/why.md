# Why?

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

### HTTP

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

### SOAP

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

### The call

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
