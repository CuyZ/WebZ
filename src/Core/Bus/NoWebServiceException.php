<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Core\Bus;

use CuyZ\WebZ\Core\Exception\WebZException;
use LogicException;

/**
 * @codeCoverageIgnore
 */
final class NoWebServiceException extends LogicException implements WebZException
{
    public function __construct()
    {
        parent::__construct('No WebService to call');
    }
}
