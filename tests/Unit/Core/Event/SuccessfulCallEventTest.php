<?php

namespace CuyZ\WebZ\Tests\Unit\Core\Event;

use CuyZ\WebZ\Core\Event\SuccessfulCallEvent;
use CuyZ\WebZ\Tests\Fixture\Mocks;
use CuyZ\WebZ\Tests\Fixture\WebService\DummyWebService;
use PHPUnit\Framework\TestCase;
use stdClass;

class SuccessfulCallEventTest extends TestCase
{
    public function test_creation()
    {
        $webService = new DummyWebService(new stdClass());
        $result = Mocks::resultOk();

        $event = new SuccessfulCallEvent($webService, $result);

        self::assertSame($webService, $event->webService);
        self::assertSame($result, $event->result);
    }
}
