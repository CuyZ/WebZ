<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Tests\Fixture\WebService;

use CuyZ\WebZ\Core\Cache\WithCache;
use CuyZ\WebZ\Core\WebService;
use CuyZ\WebZ\Http\Payload\HttpPayload;
use CuyZ\WebZ\Http\Transformer\ScalarTransformer;
use CuyZ\WebZ\Soap\SoapPayload;
use CuyZ\WebZ\Tests\Fixture\Server\HttpHandler;
use CuyZ\WebZ\Tests\Fixture\Soap\Server\FakeSoapServerClass;

final class DummyCacheWebService extends WebService implements WithCache
{
    private object $payload;
    private int $cacheLifetime;

    public function __construct(object $payload, int $cacheLifetime)
    {
        $this->payload = $payload;
        $this->cacheLifetime = $cacheLifetime;
    }

    public static function soap(string $input, int $cacheLifetime): self
    {
        return new self(
            SoapPayload::forWsdl(FakeSoapServerClass::WSDL, 'randomValue')->withArguments([$input]),
            $cacheLifetime
        );
    }

    public static function httpSingle(string $input, int $cacheLifetime): self
    {
        return new self(
            HttpPayload::request('GET', HttpHandler::route('random', ['input' => $input]))
                ->withTransformer(new ScalarTransformer()),
            $cacheLifetime
        );
    }

    public static function httpMultiplex(string $input, int $cacheLifetime): self
    {
        $payload = HttpPayload::multiplex()
            ->with('GET', HttpHandler::route('random', ['input' => $input]))
            ->with('GET', HttpHandler::route('random', ['input' => $input]))
            ->withTransformer(new ScalarTransformer());

        return new self(
            $payload,
            $cacheLifetime
        );
    }

    protected function payload(): object
    {
        return $this->payload;
    }

    public function parse(array $data)
    {
        return $data;
    }

    public function cacheLifetime(): int
    {
        return $this->cacheLifetime;
    }
}
