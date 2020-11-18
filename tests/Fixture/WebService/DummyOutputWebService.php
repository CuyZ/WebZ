<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Tests\Fixture\WebService;

use CuyZ\WebZ\Core\WebService;

final class DummyOutputWebService extends WebService
{
    private object $payload;

    /** @var mixed */
    private $output;

    public function __construct(object $payload, $output)
    {
        $this->payload = $payload;
        $this->output = $output;
    }

    protected function payload(): object
    {
        return $this->payload;
    }

    public function parse(array $data)
    {
        return $this->output;
    }
}
