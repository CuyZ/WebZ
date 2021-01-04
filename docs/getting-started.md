# Getting Started

## Installation

```bash
$ composer req cuyz/webz
```

To use the SOAP transport you need the `soap` PHP extension.

To install it see [SOAP Installation][link-soap].

## Usage

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

[link-soap]: https://www.php.net/manual/en/soap.setup.php
