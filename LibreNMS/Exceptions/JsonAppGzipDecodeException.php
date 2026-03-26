<?php

namespace LibreNMS\Exceptions;

use Throwable;

class JsonAppGzipDecodeException extends JsonAppException
{
    /**
     * @param  string  $message  The message.
     * @param  string  $output  The return from snmpget.
     * @param  int  $code  Error code.
     * @return static
     */
    public function __construct($message, private $output, $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
    }
}
