<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Core\Exception;

use LogicException;

/**
 * @codeCoverageIgnore
 */
final class NotAsyncCallException extends LogicException implements WebZException
{
    public function __construct()
    {
        parent::__construct(
            'Cannot get the payload group hash for a synchronous call',
            1605898766
        );
    }
}
