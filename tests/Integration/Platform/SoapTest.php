<?php
declare(strict_types=1);

// The namespace must be the same as the SoapTransport class so
// we can override the extension_loaded function.
namespace CuyZ\WebZ\Soap;

use CuyZ\WebZ\Soap\Exception\SoapExtensionNotInstalledException;
use PHPUnit\Framework\TestCase;

/**
 * This test HAS to be run in a separate process so that
 * the `extension_loaded` is only overridden here.
 *
 * @runTestsInSeparateProcesses
 */
final class SoapTest extends TestCase
{
    public function test_throws_if_the_soap_extension_is_missing()
    {
        $this->expectException(SoapExtensionNotInstalledException::class);

        function extension_loaded(string $name)
        {
            return false;
        }

        new SoapTransport();
    }
}
