<?php

echo ' IPOMANII-MIB ';

$oids_in = [];
$oids_out = [];

d_echo('inletConfigCurrentHigh ');
$oids_in = snmpwalk_cache_multi_oid($device, 'inletConfigCurrentHigh', $oids_in, 'IPOMANII-MIB');
d_echo('inletStatusCurrent ');
$oids_in = snmpwalk_cache_multi_oid($device, 'inletStatusCurrent', $oids_in, 'IPOMANII-MIB');
d_echo('outletConfigCurrentHigh ');
$oids_out = snmpwalk_cache_multi_oid($device, 'outletConfigCurrentHigh', $oids_out, 'IPOMANII-MIB');
d_echo('outletStatusCurrent ');
$oids_out = snmpwalk_cache_multi_oid($device, 'outletStatusCurrent', $oids_out, 'IPOMANII-MIB');

if (is_array($oids_in)) {
    foreach ($oids_in as $index => $entry) {
        $cur_oid = '.1.3.6.1.4.1.2468.1.4.2.1.3.1.3.1.3.' . $index;
        $divisor = 1000;
        $descr = (trim($pre_cache['ipoman']['in'][$index]['inletConfigDesc'], '"') != '' ? trim($pre_cache['ipoman']['in'][$index]['inletConfigDesc'], '"') : "Inlet $index");
        $current = ($entry['inletStatusCurrent'] / $divisor);
        $high_limit = ($entry['inletConfigCurrentHigh'] / 10);

        discover_sensor($valid['sensor'], 'current', $device, $cur_oid, '1.3.1.3.' . $index, 'ipoman', $descr, $divisor, '1', null, null, null, $high_limit, $current);
        // FIXME: iPoMan 1201 also says it has 2 inlets, at least until firmware 1.06 - wtf?
    }
}

if (is_array($oids_out)) {
    foreach ($oids_out as $index => $entry) {
        $cur_oid = '.1.3.6.1.4.1.2468.1.4.2.1.3.2.3.1.3.' . $index;
        $divisor = 1000;
        $descr = (trim($pre_cache['ipoman']['out'][$index]['outletConfigDesc'], '"') != '' ? trim($pre_cache['ipoman']['out'][$index]['outletConfigDesc'], '"') : "Output $index");
        $current = ($entry['outletStatusCurrent'] / $divisor);
        $high_limit = ($entry['outletConfigCurrentHigh'] / 10);

        discover_sensor($valid['sensor'], 'current', $device, $cur_oid, '2.3.1.3.' . $index, 'ipoman', $descr, $divisor, '1', null, null, null, $high_limit, $current);
    }
}
