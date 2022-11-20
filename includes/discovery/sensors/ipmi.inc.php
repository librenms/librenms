<?php

use Illuminate\Support\Str;
use LibreNMS\Config;
use LibreNMS\IPMI\IPMIClient;
use LibreNMS\IPMI\NodeManager;

if ($ipmi['host'] = get_dev_attrib($device, 'ipmi_hostname')) {
    echo 'IPMI : ';

    $ipmi['tool'] = Config::get('ipmitool', 'ipmitool');
    $ipmi['user'] = get_dev_attrib($device, 'ipmi_username');
    $ipmi['password'] = get_dev_attrib($device, 'ipmi_password');
    if (Config::get('own_hostname') == $device['hostname']) {
        $ipmi['host'] = 'localhost';
    }
    $ipmi['kg_key'] = get_dev_attrib($device, 'ipmi_kg_key');

    $client = new IPMIClient($ipmi['tool'], $ipmi['host'], $ipmi['user'], $ipmi['password'], $ipmi['kg_key']);
    foreach (Config::get('ipmi.type', []) as $ipmi_type) {
        $client->setDriver($ipmi_type);
        $results = $client->getSensors();

        $results = array_values(array_filter($results, function ($line) {
            return ! Str::contains($line, 'discrete');
        }));

        if (! empty($results)) {
            set_dev_attrib($device, 'ipmi_type', $ipmi_type);
            echo "$ipmi_type ";
            break;
        }
    }

    $index = 0;

    sort($results);
    foreach ($results as $sensor) {
        // BB +1.1V IOH     | 1.089      | Volts      | ok    | na        | 1.027     | 1.054     | 1.146     | 1.177     | na
        $values = array_map('trim', explode('|', $sensor));
        [$desc, $current, $unit, $state, $low_nonrecoverable, $low_limit, $low_warn, $high_warn, $high_limit, $high_nonrecoverable] = $values;

        if ($current != 'na' && Config::has("ipmi_unit.$unit")) {
            discover_sensor(
                $valid['sensor'],
                Config::get("ipmi_unit.$unit"),
                $device,
                $desc,
                ++$index,
                'ipmi',
                $desc,
                '1',
                '1',
                $low_limit == 'na' ? null : $low_limit,
                $low_warn == 'na' ? null : $low_warn,
                $high_warn == 'na' ? null : $high_warn,
                $high_limit == 'na' ? null : $high_limit,
                $current,
                'ipmi'
            );
        }
    }

    $nmClient = new NodeManager($client);
    if ($nmClient->isPlatformSupported()) {
        // Set Node Manager connection properties.
        foreach ($nmClient->discoverAttributes() as $nmAttribKey => $nmAttribValue) {
            set_dev_attrib($device, "node_manager_$nmAttribKey", $nmAttribValue);
        }

        $ipmi_unit_type = Config::get('ipmi_unit.Watts');
        foreach ($nmClient->discoverSensors() as $nmSensor) {
            discover_sensor(
                $valid['sensor'],
                $ipmi_unit_type,
                $device,
                $nmSensor[0],
                ++$index,
                'ipmi',
                $nmSensor[1],
                '1',
                '1',
                null,
                null,
                null,
                null,
                null,
                'ipmi'
            );
        }
    }

    echo "\n";
}

check_valid_sensors($device, 'voltage', $valid['sensor'], 'ipmi');
check_valid_sensors($device, 'temperature', $valid['sensor'], 'ipmi');
check_valid_sensors($device, 'fanspeed', $valid['sensor'], 'ipmi');
check_valid_sensors($device, 'power', $valid['sensor'], 'ipmi');
