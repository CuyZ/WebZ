<?php

namespace CuyZ\WebZ\Core\Cache;

interface WithCustomPayloadHash
{
    public function getHash(object $payload): string;
}
