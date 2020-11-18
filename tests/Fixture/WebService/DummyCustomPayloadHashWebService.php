<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Tests\Fixture\WebService;

use CuyZ\WebZ\Core\Cache\WithCustomPayloadHash;
use CuyZ\WebZ\Core\WebService;
use stdClass;

final class DummyCustomPayloadHashWebService extends WebService implements WithCustomPayloadHash
{
    private string $hash;

    public function __construct(string $hash)
    {
        $this->hash = $hash;
    }

    protected function payload(): object
    {
        return new stdClass();
    }

    public function parse(array $data)
    {
        return $data;
    }

    public function getHash(object $payload): string
    {
        return $this->hash;
    }
}
