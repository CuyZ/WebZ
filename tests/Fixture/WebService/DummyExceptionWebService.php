<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Tests\Fixture\WebService;

use CuyZ\WebZ\Core\Cache\WithCustomPayloadHash;
use CuyZ\WebZ\Core\WebService;
use CuyZ\WebZ\Tests\Fixture\Utils;
use Exception;

final class DummyExceptionWebService extends WebService implements WithCustomPayloadHash
{
    private Exception $payload;

    public function __construct(Exception $payload)
    {
        $this->payload = $payload;
    }

    protected function payload(): object
    {
        return $this->payload;
    }

    public function parse(array $data)
    {
        return $data;
    }

    public function getHash(object $payload): string
    {
        // needed to avoid the serialization of an exception
        return Utils::random('foo');
    }
}
