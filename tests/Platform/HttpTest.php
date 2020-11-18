<?php

// The namespace must be the same as the HttpTransport class so
// we can override the class_exists function.
namespace CuyZ\WebZ\Http;

use CuyZ\WebZ\Http\Exception\HttpClientNotInstalledException;
use CuyZ\WebZ\Http\HttpTransport;

it('throws if the HttpClient class is missing', function () {
    function class_exists(string $name) {
        return false;
    }

    new HttpTransport();
})->throws(HttpClientNotInstalledException::class);
