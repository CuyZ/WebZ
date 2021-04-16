<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Tests\EndToEnd;

use PHPUnit\Framework\TestCase;

abstract class ServerTestCase extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        // start server
    }

    public static function tearDownAfterClass(): void
    {
        // stop server
    }
}
