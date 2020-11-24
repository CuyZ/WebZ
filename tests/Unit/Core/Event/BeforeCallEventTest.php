<?php

namespace CuyZ\WebZ\Tests\Unit\Core\Event;

use CuyZ\WebZ\Core\Event\BeforeCallEvent;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyWebService;
use PHPUnit\Framework\TestCase;
use stdClass;

class BeforeCallEventTest extends TestCase
{
    public function test_creation()
    {
        $webService = new DummyWebService(new stdClass());

        $event = new BeforeCallEvent($webService);

        self::assertSame($webService, $event->webService);
    }
}
