# Exceptions

By default Exception are thrown as-is.

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
