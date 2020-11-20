<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Core\Exception;

use LogicException;

final class NoCompatibleAsyncTransportException extends LogicException implements WebZException
{
    public function __construct(object $payload)
    {
        parent::__construct(
            sprintf('No compatible async transport for payload of type %s', get_class($payload)),
            1605897269
        );
    }
}
