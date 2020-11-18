<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Core\Exception;

use Exception;

final class NoCompatibleTransportException extends Exception implements WebZException
{
    public function __construct(object $payload)
    {
        parent::__construct(
            sprintf('No compatible transport for payload of type %s', get_class($payload)),
            1603585170
        );
    }
}
