<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Core\Bus\Pipeline;

use CuyZ\WebZ\Core\Exception\WebZException;
use RuntimeException;

/**
 * @codeCoverageIgnore
 */
final class StackExhaustedException extends RuntimeException implements WebZException
{
    public function __construct()
    {
        parent::__construct(
            sprintf('Middleware stack exhausted with no result in "%s"', Pipeline::class),
            1605265251
        );
    }
}
