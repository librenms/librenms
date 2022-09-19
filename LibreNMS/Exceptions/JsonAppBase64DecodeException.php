<?php

namespace LibreNMS\Exceptions;

use Throwable;

class JsonAppPollingBase64DecodeException extends JsonAppException
{
    private $output;

    /**
     * @param  string  The message.
     * @param  string  The return from snmpget.
     * @param  int     Error code.
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
