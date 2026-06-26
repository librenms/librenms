<?php

use App\Facades\LibrenmsConfig;
use App\Models\Sensor;
use LibreNMS\Data\Source\Ipmitool;
use LibreNMS\Util\Number;

// IPMI - We can discover this on poll!
$sensorDiscovery = app('sensor-discovery');
if ($ipmi = Ipmitool::init()) {
    echo 'IPMI : ';

    $sensor_values = $ipmi->sensors();
    usort($sensor_values, fn ($a, $b) => $a[0] <=> $b[0]);
    $index = 0;

    foreach ($sensor_values as $values) {
        [$desc,$current,$unit,$state,$low_nonrecoverable,$low_limit,$low_warn,$high_warn,$high_limit,$high_nonrecoverable] = $values;

        if ($current != 'na' && LibrenmsConfig::has("ipmi_unit.$unit")) {
            $sensorDiscovery->discover(new Sensor([
                'poller_type' => 'ipmi',
                'sensor_class' => LibrenmsConfig::get("ipmi_unit.$unit"),
                'sensor_oid' => $desc,
                'sensor_index' => $index++,
                'sensor_type' => 'ipmi',
                'sensor_descr' => $desc,
                'sensor_limit' => $high_limit == 'na' ? null : $high_limit,
                'sensor_limit_warn' => $high_warn == 'na' ? null : $high_warn,
                'sensor_limit_low' => $low_limit == 'na' ? null : $low_limit,
                'sensor_limit_low_warn' => $low_warn == 'na' ? null : $low_warn,
                'sensor_current' => Number::cast($current),
                'rrd_type' => 'GAUGE',
            ]));
        }
    }

    echo "\n";
}

$sensorDiscovery->sync(sensor_class: 'voltage', poller_type: 'ipmi');
$sensorDiscovery->sync(sensor_class: 'temperature', poller_type: 'ipmi');
$sensorDiscovery->sync(sensor_class: 'fanspeed', poller_type: 'ipmi');
$sensorDiscovery->sync(sensor_class: 'power', poller_type: 'ipmi');
$sensorDiscovery->sync(sensor_class: 'current', poller_type: 'ipmi');
$sensorDiscovery->sync(sensor_class: 'load', poller_type: 'ipmi');
