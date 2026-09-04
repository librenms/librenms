<?php

use App\Models\Sensor;

$onuRxAtOlt = $os->onuRxAtOlt();

foreach ($os->onuOptics() as $onu) {
    if (is_numeric($onu['rx'])) {
        app('sensor-discovery')->discover(new Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'dbm',
            'sensor_oid' => '.1.3.6.1.4.1.17409.2.8.4.4.1.4.' . $onu['index'],
            'sensor_index' => $onu['index'],
            'sensor_type' => 'cdata-onu-rx',
            'sensor_descr' => $onu['name'] . ' Rx Power',
            'sensor_divisor' => 100,
            'sensor_multiplier' => 1,
            'sensor_current' => $onu['rx'] / 100,
            'sensor_limit_low' => -28,
            'sensor_limit_low_warn' => -26,
            'sensor_limit_warn' => -8,
            'sensor_limit' => -7,
            'group' => 'ONU',
        ]));
    }

    if (is_numeric($onu['tx'])) {
        app('sensor-discovery')->discover(new Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'dbm',
            'sensor_oid' => '.1.3.6.1.4.1.17409.2.8.4.4.1.5.' . $onu['index'],
            'sensor_index' => $onu['index'],
            'sensor_type' => 'cdata-onu-tx',
            'sensor_descr' => $onu['name'] . ' Tx Power',
            'sensor_divisor' => 100,
            'sensor_multiplier' => 1,
            'sensor_current' => $onu['tx'] / 100,
            'group' => 'ONU',
        ]));
    }

    // what the OLT receives from this ONU, the number that matters for a link budget
    if (is_numeric($onuRxAtOlt[$onu['onu']] ?? null)) {
        app('sensor-discovery')->discover(new Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'dbm',
            'sensor_oid' => '.1.3.6.1.4.1.17409.2.3.3.6.1.2.' . $onu['onu'],
            'sensor_index' => $onu['onu'],
            'sensor_type' => 'cdata-onu-rx-olt',
            'sensor_descr' => $onu['name'] . ' Rx Power at OLT',
            'sensor_divisor' => 100,
            'sensor_multiplier' => 1,
            'sensor_current' => $onuRxAtOlt[$onu['onu']] / 100,
            'sensor_limit_low' => -28,
            'sensor_limit_low_warn' => -26,
            'sensor_limit_warn' => -8,
            'sensor_limit' => -7,
            'group' => 'ONU',
        ]));
    }
}

foreach ($os->ponOptics() as $pon) {
    if (is_numeric($pon['tx'] ?? null)) {
        app('sensor-discovery')->discover(new Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'dbm',
            'sensor_oid' => '.1.3.6.1.4.1.17409.2.3.3.5.1.6.' . $pon['index'],
            'sensor_index' => $pon['index'],
            'sensor_type' => 'cdata-pon-tx',
            'sensor_descr' => $pon['descr'] . ' Tx Power',
            'sensor_divisor' => 100,
            'sensor_multiplier' => 1,
            'sensor_current' => $pon['tx'] / 100,
            'entPhysicalIndex' => $pon['ifIndex'],
            'entPhysicalIndex_measured' => 'ports',
            'group' => 'PON',
        ]));
    }
}

unset($onuRxAtOlt, $onu, $pon);
