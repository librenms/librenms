<?php

/*
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */

// pre-cache
$oidsOut = SnmpQuery::cache()->hideMib()->walk([
    'IPOMANII-MIB::outletConfigDesc',
    'IPOMANII-MIB::outletConfigLocation',
])->table(1);

//data
$oidsPowOut = SnmpQuery::hideMib()->walk([
    'IPOMANII-MIB::outletStatusKwatt',
])->table(1);

foreach ($oidsPowOut as $index => $entry) {
    $oid = '.1.3.6.1.4.1.2468.1.4.2.1.3.2.3.1.4.' . $index;
    $divisor = 1000;
    $descr = (trim($oidsOut[$index]['outletConfigDesc'], '"') != '' ? trim($oidsOut[$index]['outletConfigDesc'], '"') : "Output $index");

    app('sensor-discovery')->discover(new \App\Models\Sensor([
        'poller_type' => 'snmp',
        'sensor_class' => 'power_consumed',
        'sensor_oid' => $oid,
        'sensor_index' => $oid,
        'sensor_type' => 'ipoman',
        'sensor_descr' => $descr,
        'sensor_divisor' => $divisor,
        'sensor_multiplier' => 1,
        'sensor_limit_low' => 0,
        'sensor_limit_low_warn' => null,
        'sensor_limit_warn' => null,
        'sensor_limit' => 0,
        'sensor_current' => 0,
        'entPhysicalIndex' => null,
        'entPhysicalIndex_measured' => null,
        'user_func' => null,
        'group' => null,
    ]));
}
