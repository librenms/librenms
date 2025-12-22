<?php

namespace LibreNMS\Exceptions;

use Throwable;

class JsonAppMissingKeysException extends JsonAppException
{
    public function __construct($message, private $output, private $parsed_json = [], $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
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
