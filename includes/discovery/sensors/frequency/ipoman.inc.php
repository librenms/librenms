<?php

/*
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */

// pre-cache
$oidsIn = SnmpQuery::cache()->hideMib()->walk([
    'IPOMANII-MIB::inletConfigDesc',
])->table(1);

//data
$oidsFreqIn = SnmpQuery::hideMib()->walk([
    'IPOMANII-MIB::inletConfigFrequencyHigh',
    'IPOMANII-MIB::inletConfigFrequencyLow',
    'IPOMANII-MIB::inletStatusFrequency',
])->table(1);

foreach ($oidsFreqIn as $index => $entry) {
    $oid = '.1.3.6.1.4.1.2468.1.4.2.1.3.1.3.1.4.' . $index;
    $divisor = 10;
    $descr = (trim($oidsIn[$index]['inletConfigDesc'], '"') != '' ? trim($oidsIn[$index]['inletConfigDesc'], '"') : "Inlet $index");
    $value = ($entry['inletStatusFrequency'] / 10);
    $low_limit = $entry['inletConfigFrequencyLow'];
    $high_limit = $entry['inletConfigFrequencyHigh'];

    // FIXME: iPoMan 1201 also says it has 2 inlets, at least until firmware 1.06 - wtf?
    app('sensor-discovery')->discover(new \App\Models\Sensor([
        'poller_type' => 'snmp',
        'sensor_class' => 'frequency',
        'sensor_oid' => $oid,
        'sensor_index' => $index,
        'sensor_type' => 'ipoman',
        'sensor_descr' => $descr,
        'sensor_divisor' => $divisor,
        'sensor_multiplier' => 1,
        'sensor_limit_low' => $low_limit,
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
