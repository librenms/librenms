<?php

use App\Models\Sensor;

foreach ($os->onuOptics() as $onu) {
    if (is_numeric($onu['voltage'])) {
        app('sensor-discovery')->discover(new Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'voltage',
            'sensor_oid' => '.1.3.6.1.4.1.17409.2.8.4.4.1.7.' . $onu['index'],
            'sensor_index' => $onu['index'],
            'sensor_type' => 'cdata-onu',
            'sensor_descr' => $onu['name'] . ' Voltage',
            'sensor_divisor' => 100,
            'sensor_multiplier' => 1,
            'sensor_current' => $onu['voltage'] / 100,
            'sensor_limit_low' => 3.0,
            'sensor_limit_low_warn' => 3.1,
            'sensor_limit_warn' => 3.5,
            'sensor_limit' => 3.6,
            'group' => 'ONU',
        ]));
    }
}

foreach ($os->ponOptics() as $pon) {
    if (is_numeric($pon['voltage'] ?? null)) {
        app('sensor-discovery')->discover(new Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'voltage',
            'sensor_oid' => '.1.3.6.1.4.1.17409.2.3.3.5.1.4.' . $pon['index'],
            'sensor_index' => $pon['index'],
            'sensor_type' => 'cdata-pon',
            'sensor_descr' => $pon['descr'] . ' Transceiver',
            'sensor_divisor' => 100,
            'sensor_multiplier' => 1,
            'sensor_current' => $pon['voltage'] / 100,
            'sensor_limit_low' => 3.0,
            'sensor_limit_low_warn' => 3.1,
            'sensor_limit_warn' => 3.5,
            'sensor_limit' => 3.6,
            'entPhysicalIndex' => $pon['ifIndex'],
            'entPhysicalIndex_measured' => 'ports',
            'group' => 'PON',
        ]));
    }
}

unset($onu, $pon);
