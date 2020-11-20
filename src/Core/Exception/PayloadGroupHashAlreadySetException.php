<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Core\Exception;

use LogicException;

final class PayloadGroupHashAlreadySetException extends LogicException implements WebZException
{
    public function __construct()
    {
        parent::__construct(
            'The payload group hash cannot be overridden',
            1605898597
        );
    }
}
