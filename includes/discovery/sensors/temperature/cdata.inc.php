<?php

use App\Models\Sensor;

foreach ($os->onuOptics() as $onu) {
    if (is_numeric($onu['temperature'])) {
        app('sensor-discovery')->discover(new Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'temperature',
            'sensor_oid' => '.1.3.6.1.4.1.17409.2.8.4.4.1.8.' . $onu['index'],
            'sensor_index' => $onu['index'],
            'sensor_type' => 'cdata-onu',
            'sensor_descr' => $onu['name'] . ' Temperature',
            'sensor_divisor' => 100,
            'sensor_multiplier' => 1,
            'sensor_current' => $onu['temperature'] / 100,
            'sensor_limit_warn' => 75,
            'sensor_limit' => 85,
            'group' => 'ONU',
        ]));
    }
}

foreach ($os->ponOptics() as $pon) {
    if (is_numeric($pon['temperature'] ?? null)) {
        app('sensor-discovery')->discover(new Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'temperature',
            'sensor_oid' => '.1.3.6.1.4.1.17409.2.3.3.5.1.100.' . $pon['index'],
            'sensor_index' => $pon['index'],
            'sensor_type' => 'cdata-pon',
            'sensor_descr' => $pon['descr'] . ' Transceiver',
            'sensor_divisor' => 100,
            'sensor_multiplier' => 1,
            'sensor_current' => $pon['temperature'] / 100,
            'sensor_limit_warn' => 70,
            'sensor_limit' => 80,
            'entPhysicalIndex' => $pon['ifIndex'],
            'entPhysicalIndex_measured' => 'ports',
            'group' => 'PON',
        ]));
    }
}

unset($onu, $pon);
