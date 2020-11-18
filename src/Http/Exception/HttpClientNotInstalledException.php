<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Http\Exception;

use CuyZ\WebZ\Core\Exception\WebZException;
use CuyZ\WebZ\Http\HttpTransport;
use Exception;

/**
 * @codeCoverageIgnore
 */
final class HttpClientNotInstalledException extends Exception implements WebZException
{
    public function __construct()
    {
        parent::__construct(
            sprintf(
                'Using "%s" requires that the Symfony HttpClient component version 4.4 or higher is installed, try running "composer require symfony/http-client".',
                HttpTransport::class
            ),
            1605262518
        );
    }
}
