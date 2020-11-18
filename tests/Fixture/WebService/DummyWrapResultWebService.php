<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Tests\Fixture\WebService;

use CuyZ\WebZ\Core\WebService;
use CuyZ\WebZ\Tests\Fixture\FakeResult;

final class DummyWrapResultWebService extends WebService
{
    private object $payload;

    public function __construct(object $payload)
    {
        $this->payload = $payload;
    }

    protected function payload(): object
    {
        return $this->payload;
    }

    public function parse(array $data): FakeResult
    {
        return new FakeResult($data);
    }
}
