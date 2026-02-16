<?php

namespace LibreNMS\Exceptions;

class HostNameEmptyException extends HostExistsException
{
    /**
     * @var string
     */
    public $hostname;

    public function __construct()
    {
        $message = trans('exceptions.host_name_empty');

        parent::__construct($message);
    }
}
