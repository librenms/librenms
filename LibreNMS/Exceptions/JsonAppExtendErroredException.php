<?php

namespace LibreNMS\Exceptions;

use Throwable;

class JsonAppExtendErroredException extends JsonAppException
{
    private $output;
    private $parsed_json;

    public function __construct($message, $output, $parsed_json = [], $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->output = $output;
        $this->parsed_json = $parsed_json;
    }

    public function getOutput()
    {
        return $this->output;
    }

    public function getParsedJson()
    {
        return $this->parsed_json;
    }
}
