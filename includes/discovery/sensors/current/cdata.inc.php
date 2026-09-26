<?php

use App\Models\Sensor;

foreach ($os->onuOptics() as $onu) {
    if (is_numeric($onu['bias'])) {
        app('sensor-discovery')->discover(new Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'current',
            'sensor_oid' => '.1.3.6.1.4.1.17409.2.8.4.4.1.6.' . $onu['index'],
            'sensor_index' => $onu['index'],
            'sensor_type' => 'cdata-onu',
            'sensor_descr' => $onu['name'] . ' Bias Current',
            'sensor_divisor' => 100000, // centi-mA to A
            'sensor_multiplier' => 1,
            'sensor_current' => $onu['bias'] / 100000,
            'group' => 'ONU',
        ]));
    }
}

foreach ($os->ponOptics() as $pon) {
    if (is_numeric($pon['bias'] ?? null)) {
        app('sensor-discovery')->discover(new Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'current',
            'sensor_oid' => '.1.3.6.1.4.1.17409.2.3.3.5.1.5.' . $pon['index'],
            'sensor_index' => $pon['index'],
            'sensor_type' => 'cdata-pon',
            'sensor_descr' => $pon['descr'] . ' Bias Current',
            'sensor_divisor' => 100000,
            'sensor_multiplier' => 1,
            'sensor_current' => $pon['bias'] / 100000,
            'entPhysicalIndex' => $pon['ifIndex'],
            'entPhysicalIndex_measured' => 'ports',
            'group' => 'PON',
        ]));
    }
}

unset($onu, $pon);
