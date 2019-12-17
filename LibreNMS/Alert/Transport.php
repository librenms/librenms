<?php

namespace LibreNMS\Alert;

use LibreNMS\Interfaces\Alert\Transport as TransportInterface;
use LibreNMS\Config;

abstract class Transport implements TransportInterface
{
    protected $config;

    /**
     * Transport constructor.
     * @param null $transport_id
     */
    public function __construct($transport_id = null)
    {
        if (!empty($transport_id)) {
            $sql = "SELECT `transport_config` FROM `alert_transports` WHERE `transport_id`=?";
            $this->config = json_decode(dbFetchCell($sql, [$transport_id]), true);
        }
    }

    /**
     * Helper function to parse free form text box defined in ini style to key value pairs
     *
     * @param string $input
     * @return array
     */
    protected function parseUserOptions($input)
    {
        $options = [];
        foreach (explode(PHP_EOL, $input) as $option) {
            if (str_contains($option, '=')) {
                list($k,$v) = explode('=', $option, 2);
                $options[$k] = trim($v);
            }
        }
        return $options;
    }

        /**
     * Get the hex color string for a particular state
     * @param integer $state State code from alert
     * @return string Hex color, default to #337AB7 blue if state unrecognised
     */
    public static function getColorForState($state)
    {
        $colors = array(
            0 => Config::get('alert_colour.ok'),
            1 => Config::get('alert_colour.bad'),
            2 => Config::get('alert_colour.acknowledged'),
            3 => Config::get('alert_colour.worse'),
            4 => Config::get('alert_colour.better'),
        );

        return isset($colors[$state]) ? $colors[$state] : '#337AB7';
    }
}
