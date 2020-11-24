<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Soap\Exception;

use CuyZ\WebZ\Core\Exception\WebZException;
use LogicException;

/**
 * @codeCoverageIgnore
 */
final class MissingSoapActionException extends LogicException implements WebZException
{
    public function __construct()
    {
        parent::__construct('The SOAP action is missing from the payload', 1605304939);
    }
}
