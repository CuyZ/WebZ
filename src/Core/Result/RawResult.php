<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Core\Result;

use Exception;

/**
 * @immutable
 */
final class RawResult
{
    private array $data;
    private ?Exception $exception = null;
    private ?string $requestTrace = null;
    private ?string $responseTrace = null;

    public static function ok(array $data = []): self
    {
        return new self($data);
    }

    public static function err(Exception $exception, array $data = []): self
    {
        $result = new self($data);
        $result->exception = $exception;

        return $result;
    }

    private function __construct(array $data)
    {
        $this->data = $data;
    }

    public function withRequestTrace(?string $requestTrace): self
    {
        $result = clone $this;
        $result->requestTrace = $requestTrace;

        return $result;
    }

    public function withResponseTrace(?string $responseTrace): self
    {
        $result = clone $this;
        $result->responseTrace = $responseTrace;

        return $result;
    }

    public function data(): array
    {
        return $this->data;
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
}
