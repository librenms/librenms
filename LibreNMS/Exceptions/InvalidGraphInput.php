<?php

namespace LibreNMS\Exceptions;

/**
 * The graph input failed the graph's validation rules
 */
class InvalidGraphInput extends GraphException
{
    public function __construct(string $message)
    {
        parent::__construct($message, 'Invalid Input');
    }
}
