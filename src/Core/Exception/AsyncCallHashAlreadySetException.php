<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Core\Exception;

use LogicException;

/**
 * @codeCoverageIgnore
 */
final class AsyncCallHashAlreadySetException extends LogicException implements WebZException
{
    public function __construct()
    {
        parent::__construct(
            'The async call hash cannot be overridden',
            1605898597
        );
    }
}
