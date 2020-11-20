<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Core\Result;

use CuyZ\WebZ\Core\Support\Timer;
use Exception;

/**
 * @immutable
 */
final class Result
{
    private array $data;
    private Timer $timer;
    private ?Exception $exception;
    private ?string $requestTrace;
    private ?string $responseTrace;
    private bool $comesFromCache;

    public function __construct(RawResult $rawResult, Timer $timer)
    {
        $this->data = $rawResult->data();
        $this->exception = $rawResult->exception();
        $this->requestTrace = $rawResult->requestTrace();
        $this->responseTrace = $rawResult->responseTrace();
        $this->comesFromCache = false;

        $this->timer = $timer;
    }

    public function data(): array
    {
        return $this->data;
    }

    public function timer(): Timer
    {
        return $this->timer;
    }

    public function exception(): ?Exception
    {
        return $this->exception;
    }

    public function requestTrace(): ?string
    {
        return $this->requestTrace;
    }

    public function responseTrace(): ?string
    {
        return $this->responseTrace;
    }

    public function isFromCache(): bool
    {
        return $this->comesFromCache;
    }

    public function withData(array $data): self
    {
        $result = clone $this;
        $result->data = $data;

        return $result;
    }

    public function markAsComingFromCache(): self
    {
        $result = clone $this;
        $result->comesFromCache = true;
        return $result;
    }
}
