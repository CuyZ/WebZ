<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Soap\Exception;

use CuyZ\WebZ\Core\Exception\WebZException;
use LogicException;

final class MissingSoapMethodException extends LogicException implements WebZException
{
    public function __construct()
    {
        parent::__construct('The SOAP method is missing from the payload', 1605304939);
    }
}
