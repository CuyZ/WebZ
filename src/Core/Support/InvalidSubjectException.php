<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Core\Support;

use CuyZ\WebZ\Core\Exception\WebZException;
use InvalidArgumentException;

/**
 * @codeCoverageIgnore
 */
final class InvalidSubjectException extends InvalidArgumentException implements WebZException
{
    /**
     * @param mixed $subject
     */
    public function __construct($subject)
    {
        parent::__construct(
            '$subject must be an object or an array, `' . gettype($subject) . '` given',
            1507292680
        );
    }
}
