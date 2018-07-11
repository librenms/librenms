<?php

namespace LibreNMS\Alert;

use LibreNMS\Interfaces\Alert\Transport as TransportInterface;

abstract class Transport implements TransportInterface
{
    protected $config;

    // Sets config field to an associative array of transport config values
    public function __construct($transport_id = null)
    {
        if (!empty($transport_id)) {
            $sql = "SELECT `transport_config` FROM `alert_transports` WHERE `transport_id`=?";
            $this->config = json_decode(dbFetchCell($sql, [$transport_id]), true);
        }
    }
}
