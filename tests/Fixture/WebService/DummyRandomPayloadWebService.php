<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Tests\Fixture\WebService;

use CuyZ\WebZ\Core\WebService;
use CuyZ\WebZ\Tests\Fixture\Utils;
use stdClass;

final class DummyRandomPayloadWebService extends WebService
{
    protected function payload(): object
    {
        $payload = new stdClass();
        $payload->foo = Utils::random('foo');

        return $payload;
    }

    public function parse(array $data)
    {
        return $data;
    }
}
