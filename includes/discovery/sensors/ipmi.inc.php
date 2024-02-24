<?php

use Illuminate\Support\Str;
use LibreNMS\Config;

// IPMI - We can discover this on poll!
if ($ipmi['host'] = get_dev_attrib($device, 'ipmi_hostname')) {
    echo 'IPMI : ';
    $ipmi['port'] = filter_var(get_dev_attrib($device, 'ipmi_port'), FILTER_VALIDATE_INT) ?: '623';
    $ipmi['user'] = get_dev_attrib($device, 'ipmi_username');
    $ipmi['password'] = get_dev_attrib($device, 'ipmi_password');
    $ipmi['kg_key'] = get_dev_attrib($device, 'ipmi_kg_key');

    $cmd = [Config::get('ipmitool', 'ipmitool')];
    if (Config::get('own_hostname') != $device['hostname'] || $ipmi['host'] != 'localhost') {
        if (empty($ipmi['kg_key']) || is_null($ipmi['kg_key'])) {
            array_push($cmd, '-H', $ipmi['host'], '-p', $ipmi['port'], '-U', $ipmi['user'], '-P', $ipmi['password'], '-L', 'USER');
        } else {
            array_push($cmd, '-H', $ipmi['host'], '-p', $ipmi['port'], '-U', $ipmi['user'], '-P', $ipmi['password'], '-y', $ipmi['kg_key'], '-L', 'USER');
        }
    }

    foreach (Config::get('ipmi.type', []) as $ipmi_type) {
        $results = explode(PHP_EOL, external_exec(array_merge($cmd, ['-I', $ipmi_type, 'sensor'])));

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
        [$desc,$current,$unit,$state,$low_nonrecoverable,$low_limit,$low_warn,$high_warn,$high_limit,$high_nonrecoverable] = $values;

        $index++;
        if ($current != 'na' && Config::has("ipmi_unit.$unit")) {
            discover_sensor(
                $valid['sensor'],
                Config::get("ipmi_unit.$unit"),
                $device,
                $desc,
                $index,
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

    echo "\n";
}

check_valid_sensors($device, 'voltage', $valid['sensor'], 'ipmi');
check_valid_sensors($device, 'temperature', $valid['sensor'], 'ipmi');
check_valid_sensors($device, 'fanspeed', $valid['sensor'], 'ipmi');
check_valid_sensors($device, 'power', $valid['sensor'], 'ipmi');
