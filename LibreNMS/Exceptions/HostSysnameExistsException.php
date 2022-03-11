<?php

namespace LibreNMS\Exceptions;

class HostSysnameExistsException extends HostExistsException
{
    /**
     * @var string
     */
    public $hostname;
    /**
     * @var string
     */
    public $sysname;

    public function __construct(string $hostname, string $sysname)
    {
        $this->hostname = $hostname;
        $this->sysname = $sysname;

        $message = trans('exceptions.host_exists.sysname_exists', [
            'hostname' => $hostname,
            'sysname' => $sysname,
        ]);

        parent::__construct($message);
    }
}
