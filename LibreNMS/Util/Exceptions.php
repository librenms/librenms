<?php

namespace LibreNMS\Util;

use ErrorException;

class Exceptions
{
    private const FATAL_ERROR_MASK = E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR | E_RECOVERABLE_ERROR;

    public static function isFatalError(ErrorException $e): bool
    {
        return (bool) ($e->getSeverity() & self::FATAL_ERROR_MASK);
    }
}
