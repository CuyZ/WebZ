<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Http\Payload;

use CuyZ\WebZ\Http\Transformer\Transformer;

final class MultiplexPayload extends HttpPayload
{
    private ?float $streamTimeout;

    /** @var RequestPayload[] */
    private array $requests = [];

    public function __construct(?float $streamTimeout = null)
    {
        $this->streamTimeout = $streamTimeout;
    }

    public function streamTimeout(): ?float
    {
        return $this->streamTimeout;
    }

    public function with(string $method, string $url, array $options = [], ?Transformer $transformer = null): self
    {
        $request = new RequestPayload($method, $url);
        $request->withOptions($options);

        if ($transformer instanceof Transformer) {
            $request->withTransformer($transformer);
        }

        $this->requests[] = $request;
        return $this;
    }

    /**
     * @return RequestPayload[]
     */
    public function requests(): array
    {
        return $this->requests;
    }
}
