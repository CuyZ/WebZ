<?php

use CuyZ\WebZ\Core\Bus\Bus;
use CuyZ\WebZ\Core\Transport\NoTransportException;

it('throws when no transport is configured', function () {
    Bus::builder()->build();
})->throws(NoTransportException::class);
