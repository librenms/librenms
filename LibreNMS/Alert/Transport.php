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

    // Build the config from vars and template
    public static function configBuilder($vars, $tmp) {
        $transport_config = [];

        foreach ($tmp as $item) {
            $name = $item['name'];
            
            if (!isset($vars[$name])) {
                if ($item['required']) {
                    return [];
                } else {
                    continue;
                }
            }
            $transport_config[$name] = $vars[$name];
        }
        return $transport_config;
    }
}
