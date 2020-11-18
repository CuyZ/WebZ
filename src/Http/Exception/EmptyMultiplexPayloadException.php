<?php
declare(strict_types=1);

namespace CuyZ\WebZ\Http\Exception;

use CuyZ\WebZ\Core\Exception\WebZException;
use LogicException;

final class EmptyMultiplexPayloadException extends LogicException implements WebZException
{
}
