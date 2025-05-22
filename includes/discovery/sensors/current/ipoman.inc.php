<?php

/*
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */

// pre-cache
$oidsOut = SnmpQuery::cache()->hideMib()->walk([
    'IPOMANII-MIB::outletConfigDesc',
    'IPOMANII-MIB::outletConfigLocation',
])->table(1);
$oidsIn = SnmpQuery::cache()->hideMib()->walk([
    'IPOMANII-MIB::inletConfigDesc',
])->table(1);

//data
$oidsCurrIn = SnmpQuery::hideMib()->walk([
    'IPOMANII-MIB::inletConfigCurrentHigh',
    'IPOMANII-MIB::inletStatusCurrent',
])->table(1);
$oidsCurrOut = SnmpQuery::hideMib()->walk([
    'IPOMANII-MIB::outletConfigCurrentHigh',
    'IPOMANII-MIB::outletStatusCurrent',
])->table(1);

foreach ($oidsCurrIn as $index => $entry) {
    $oid = '.1.3.6.1.4.1.2468.1.4.2.1.3.1.3.1.3.' . $index;
    $divisor = 1000;
    $descr = (trim($oidsIn[$index]['inletConfigDesc'], '"') != '' ? trim($oidsIn[$index]['inletConfigDesc'], '"') : "Inlet $index");
    $value = ($entry['inletStatusCurrent'] / $divisor);
    $high_limit = ($entry['inletConfigCurrentHigh'] / 10);

    // FIXME: iPoMan 1201 also says it has 2 inlets, at least until firmware 1.06 - wtf?
    app('sensor-discovery')->discover(new \App\Models\Sensor([
        'poller_type' => 'snmp',
        'sensor_class' => 'current',
        'sensor_oid' => $oid,
        'sensor_index' => '1.3.1.3.' . $index,
        'sensor_type' => 'ipoman',
        'sensor_descr' => $descr,
        'sensor_divisor' => $divisor,
        'sensor_multiplier' => 1,
        'sensor_limit_low' => null,
        'sensor_limit_low_warn' => null,
        'sensor_limit_warn' => null,
        'sensor_limit' => $high_limit,
        'sensor_current' => $value,
        'entPhysicalIndex' => null,
        'entPhysicalIndex_measured' => null,
        'user_func' => null,
        'group' => null,
    ]));
}

foreach ($oidsCurrOut as $index => $entry) {
    $oid = '.1.3.6.1.4.1.2468.1.4.2.1.3.2.3.1.3.' . $index;
    $divisor = 1000;
    $descr = (trim($oidsOut[$index]['outletConfigDesc'], '"') != '' ? trim($oidsOut[$index]['outletConfigDesc'], '"') : "Output $index");
    $value = ($entry['outletStatusCurrent'] / $divisor);
    $high_limit = ($entry['outletConfigCurrentHigh'] / 10);

    app('sensor-discovery')->discover(new \App\Models\Sensor([
        'poller_type' => 'snmp',
        'sensor_class' => 'current',
        'sensor_oid' => $oid,
        'sensor_index' => '2.3.1.3.' . $index,
        'sensor_type' => 'ipoman',
        'sensor_descr' => $descr,
        'sensor_divisor' => $divisor,
        'sensor_multiplier' => 1,
        'sensor_limit_low' => null,
        'sensor_limit_low_warn' => null,
        'sensor_limit_warn' => null,
        'sensor_limit' => $high_limit,
        'sensor_current' => $value,
        'entPhysicalIndex' => null,
        'entPhysicalIndex_measured' => null,
        'user_func' => null,
        'group' => null,
    ]));
}
