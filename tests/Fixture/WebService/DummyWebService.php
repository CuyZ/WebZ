<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Tests\Fixture\WebService;

use CuyZ\WebZ\Core\WebService;
use stdClass;

final class DummyWebService extends WebService
{
    private object $payload;

    public function __construct(object $payload = null)
    {
        $this->payload = $payload ?? new stdClass();
    }

    protected function payload(): object
    {
        return $this->payload;
    }

    public function parse(array $data)
    {
        return $data;
    }
}
