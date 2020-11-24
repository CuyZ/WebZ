<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Http\Exception;

use CuyZ\WebZ\Core\Exception\WebZException;
use LogicException;

/**
 * @codeCoverageIgnore
 */
final class MissingConfigException extends LogicException implements WebZException
{
    public function __construct(string $option)
    {
        parent::__construct(
            sprintf('The option "%s" is missing', $option),
            1605697004
        );
    }
}
