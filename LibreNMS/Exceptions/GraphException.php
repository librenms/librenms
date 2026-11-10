<?php

namespace LibreNMS\Exceptions;

use Throwable;

/**
 * A graph could not be produced. The short text is shown instead of the message on small graphs.
 */
class GraphException extends \Exception
{
    public function __construct(string $message = '', public readonly ?string $shortText = null, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }

    /**
     * The HTTP status code to respond with
     */
    public function httpStatus(): int
    {
        return 500;
    }
}
