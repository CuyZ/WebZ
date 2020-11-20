<?php

// The namespace must be the same as the SoapTransport class so
// we can override the extension_loaded function.
namespace CuyZ\WebZ\Soap;

use CuyZ\WebZ\Soap\Exception\SoapExtensionNotInstalledException;

it('throws if the ext-soap extension is missing', function () {
    function extension_loaded(string $name)
    {
        return false;
    }

    new SoapTransport();
})->throws(SoapExtensionNotInstalledException::class);
