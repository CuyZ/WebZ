<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Soap\Exception;

use CuyZ\WebZ\Core\Exception\WebZException;
use CuyZ\WebZ\Soap\SoapTransport;
use LogicException;

/**
 * @codeCoverageIgnore
 */
final class SoapExtensionNotInstalledException extends LogicException implements WebZException
{
    public function __construct()
    {
        parent::__construct(
            sprintf(
                'Using "%s" requires the ext-soap extension',
                SoapTransport::class
            ),
            1605261983
        );
    }
}
