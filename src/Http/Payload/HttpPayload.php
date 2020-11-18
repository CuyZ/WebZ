<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Http\Payload;

use CuyZ\WebZ\Http\Transformer\AutoTransformer;
use CuyZ\WebZ\Http\Transformer\Transformer;

abstract class HttpPayload
{
    private ?Transformer $transformer = null;

    public static function request(string $method, string $url): RequestPayload
    {
        return new RequestPayload($method, $url);
    }

    public static function multiplex(?float $streamTimeout = null): MultiplexPayload
    {
        return new MultiplexPayload($streamTimeout);
    }

    public function withTransformer(Transformer $transformer): self
    {
        $this->transformer = $transformer;
        return $this;
    }

    public function transformer(): ?Transformer
    {
        return $this->transformer;
    }
}
