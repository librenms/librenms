<?php

namespace LibreNMS\Exceptions;

/**
 * The current user can not view the graph
 */
class UnauthorizedGraph extends GraphException
{
    public function __construct()
    {
        parent::__construct('No Authorization', 'No Auth');
    }

    public function httpStatus(): int
    {
        return 403;
    }
}
