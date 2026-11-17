<?php

namespace LibreNMS\Exceptions;

use Throwable;

/**
 * There is nothing to draw, such as a device without the entities or rrd files the graph needs
 */
class NoGraphData extends GraphException
{
    public function __construct(string $message = 'No Data', ?Throwable $previous = null)
    {
        parent::__construct($message, 'No Data', $previous);
    }
}
