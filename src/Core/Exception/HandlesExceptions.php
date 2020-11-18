<?php

namespace CuyZ\WebZ\Core\Exception;

use Exception;

interface HandlesExceptions
{
    public function onException(Exception $e): void;
}
