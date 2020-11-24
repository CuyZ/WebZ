<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Core\Cache;

use CuyZ\WebZ\Core\Exception\WebZException;
use CuyZ\WebZ\Core\Result\Result;
use UnexpectedValueException;

/**
 * @codeCoverageIgnore
 */
final class CorruptCacheEntryException extends UnexpectedValueException implements WebZException
{
    public function __construct(string $hash)
    {
        parent::__construct(
            sprintf(
                'The cache entry "%s" is not an instance of "%s"',
                $hash,
                Result::class,
            ),
            1605636005
        );
    }
}
