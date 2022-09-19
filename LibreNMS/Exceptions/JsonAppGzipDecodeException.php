<?php

namespace LibreNMS\Exceptions;

use Throwable;

class JsonAppPollingGzipDecodeException extends JsonAppException
{
    private $output;

    /**
     * @param  string  $message  The message.
     * @param  string  $output   The return from snmpget.
     * @param  int     $code     Error code.
     * @return static
     */
    public function __construct($message, $output, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->output = $output;
    }

    /**
     * @return string
     */
    public function getOutput()
    {
        return $this->output;
    }
}
