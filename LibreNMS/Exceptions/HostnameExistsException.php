<?php

namespace LibreNMS\Exceptions;

class HostnameExistsException extends HostExistsException
{
    /**
     * @var string
     */
    public $hostname;
    /**
     * @var string
     */
    public $existing;

    public function __construct(string $hostname)
    {
        $this->hostname = $hostname;

        $message = trans('exceptions.host_exists.hostname_exists', [
            'hostname' => $hostname,
        ]);

        parent::__construct($message);
    }
}
