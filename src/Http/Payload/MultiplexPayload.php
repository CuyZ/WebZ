<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Http\Payload;

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

    public function with(RequestPayload $request): self
    {
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
