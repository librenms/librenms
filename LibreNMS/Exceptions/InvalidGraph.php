<?php

namespace LibreNMS\Exceptions;

/**
 * The requested graph does not exist
 */
class InvalidGraph extends GraphException
{
    public function __construct()
    {
        parent::__construct('Invalid Graph', 'Invalid Graph');
    }

    public function httpStatus(): int
    {
        return 404;
    }
}
