# Cache

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

For example with [symfony/cache][link-cache]:

```php
use CuyZ\WebZ\Core\Bus\WebServiceBus;
use CuyZ\WebZ\Http\HttpTransport;
use CuyZ\WebZ\Soap\SoapTransport;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Psr16Cache;

$pool = new Psr16Cache(new ArrayAdapter());

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

[link-psr-simple-cache]: https://packagist.org/providers/psr/simple-cache-implementation
[link-cache]: https://symfony.com/doc/current/components/cache.html
