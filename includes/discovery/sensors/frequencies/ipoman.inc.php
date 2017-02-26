<?php

echo 'IPOMANII-MIB: ';

if (!is_array($cache['ipoman'])) {
    d_echo('outletConfigDesc ');
    $cache['ipoman']['out'] = snmpwalk_cache_multi_oid($device, 'outletConfigDesc', $cache['ipoman']['out'], 'IPOMANII-MIB');
    d_echo('outletConfigLocation ');
    $cache['ipoman']['out'] = snmpwalk_cache_multi_oid($device, 'outletConfigLocation', $cache['ipoman']['out'], 'IPOMANII-MIB');
    d_echo('inletConfigDesc ');
    $cache['ipoman']['in'] = snmpwalk_cache_multi_oid($device, 'inletConfigDesc', $cache['ipoman']['in'], 'IPOMANII-MIB');
}

$oids = array();

d_echo('inletConfigFrequencyHigh ');
$oids = snmpwalk_cache_multi_oid($device, 'inletConfigFrequencyHigh', $oids, 'IPOMANII-MIB');
d_echo('inletConfigFrequencyLow ');
$oids = snmpwalk_cache_multi_oid($device, 'inletConfigFrequencyLow', $oids, 'IPOMANII-MIB');
d_echo('inletStatusFrequency ');
$oids = snmpwalk_cache_multi_oid($device, 'inletStatusFrequency', $oids, 'IPOMANII-MIB');

if (is_array($oids)) {
    foreach ($oids as $index => $entry) {
        $freq_oid   = '.1.3.6.1.4.1.2468.1.4.2.1.3.1.3.1.4.'.$index;
        $divisor    = 10;
        $descr      = (trim($cache['ipoman']['in'][$index]['inletConfigDesc'], '"') != '' ? trim($cache['ipoman']['in'][$index]['inletConfigDesc'], '"') : "Inlet $index");
        $current    = ($entry['inletStatusFrequency'] / 10);
        $low_limit  = $entry['inletConfigFrequencyLow'];
        $high_limit = $entry['inletConfigFrequencyHigh'];
        discover_sensor($valid['sensor'], 'frequency', $device, $freq_oid, $index, 'ipoman', $descr, $divisor, '1', $low_limit, null, null, $high_limit, $current);
        // FIXME: iPoMan 1201 also says it has 2 inlets, at least until firmware 1.06 - wtf?
    }
}
