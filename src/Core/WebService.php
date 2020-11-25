<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Core;

use CuyZ\WebZ\Core\Cache\WithCustomPayloadHash;
use CuyZ\WebZ\Core\Exception\NotAsyncCallException;
use CuyZ\WebZ\Core\Exception\AsyncCallHashAlreadySetException;

abstract class WebService
{
    private ?object $payload = null;
    private ?string $hash = null;
    private ?string $asyncCallHash = null;

    abstract protected function payload(): object;

    /**
     * @param array $data
     * @return mixed
     */
    abstract public function parse(array $data);

    final public function getPayload(): object
    {
        return $this->payload ??= $this->payload();
    }

    final public function getPayloadHash(): string
    {
        if ($this instanceof WithCustomPayloadHash) {
            return $this->hash ??= $this->getHash($this->getPayload());
        }

        return $this->hash ??= sha1(serialize($this->getPayload()));
    }

    final public function isAsyncCall(): bool
    {
        return strlen((string)$this->asyncCallHash) > 0;
    }

    final public function markAsAsyncCall(string $asyncCallHash): void
    {
        if (strlen((string)$this->asyncCallHash) > 0) {
            throw new AsyncCallHashAlreadySetException();
        }

        $this->asyncCallHash = $asyncCallHash;
    }

    final public function getAsyncCallHash(): ?string
    {
        if (!$this->isAsyncCall()) {
            throw new NotAsyncCallException();
        }

        return $this->asyncCallHash;
    }
}
