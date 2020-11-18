<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Tests\Fixture\WebService;

use CuyZ\WebZ\Core\Exception\HandlesExceptions;
use CuyZ\WebZ\Core\WebService;
use Exception;
use stdClass;

final class DummyExceptionsWebService extends WebService implements HandlesExceptions
{
    private string $message;

    public function __construct(string $message = 'foo')
    {
        $this->message = $message;
    }

    protected function payload(): object
    {
        return new stdClass();
    }

    public function parse(array $data)
    {
        //
    }

    public function onException(Exception $e): void
    {
        throw new Exception($this->message);
    }
}
