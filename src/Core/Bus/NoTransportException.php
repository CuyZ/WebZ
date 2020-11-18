<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Core\Bus;

use CuyZ\WebZ\Core\Exception\WebZException;
use LogicException;

final class NoTransportException extends LogicException implements WebZException
{
    public function __construct()
    {
        parent::__construct('Cannot build a Bus without any transport');
    }
}
