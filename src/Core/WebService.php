<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Core;

use CuyZ\WebZ\Core\Cache\WithCustomPayloadHash;

abstract class WebService
{
    private ?object $payload = null;
    private ?string $hash = null;

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
}
