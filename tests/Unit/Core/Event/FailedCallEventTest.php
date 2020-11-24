<?php

namespace CuyZ\WebZ\Tests\Unit\Core\Event;

use CuyZ\WebZ\Core\Event\FailedCallEvent;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyWebService;
use Exception;
use PHPUnit\Framework\TestCase;
use stdClass;

class FailedCallEventTest extends TestCase
{
    public function test_creation()
    {
        $webService = new DummyWebService(new stdClass());
        $exception = new Exception();

        $event = new FailedCallEvent($webService, $exception);

        self::assertSame($webService, $event->webService);
        self::assertSame($exception, $event->exception);
    }
}
