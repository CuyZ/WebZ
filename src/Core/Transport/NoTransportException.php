<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Core\Transport;

use CuyZ\WebZ\Core\Exception\WebZException;
use LogicException;

final class NoTransportException extends LogicException implements WebZException
{
    public function __construct()
    {
        parent::__construct('No transport was configured');
    }
}
