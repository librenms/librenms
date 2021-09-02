<?php

echo 'IPOMANII-MIB: ';

$oids = [];

d_echo('inletConfigFrequencyHigh ');
$oids = snmpwalk_cache_multi_oid($device, 'inletConfigFrequencyHigh', $oids, 'IPOMANII-MIB');
d_echo('inletConfigFrequencyLow ');
$oids = snmpwalk_cache_multi_oid($device, 'inletConfigFrequencyLow', $oids, 'IPOMANII-MIB');
d_echo('inletStatusFrequency ');
$oids = snmpwalk_cache_multi_oid($device, 'inletStatusFrequency', $oids, 'IPOMANII-MIB');

if (is_array($oids)) {
    foreach ($oids as $index => $entry) {
        $freq_oid = '.1.3.6.1.4.1.2468.1.4.2.1.3.1.3.1.4.' . $index;
        $divisor = 10;
        $descr = (trim($pre_cache['ipoman']['in'][$index]['inletConfigDesc'], '"') != '' ? trim($pre_cache['ipoman']['in'][$index]['inletConfigDesc'], '"') : "Inlet $index");
        $current = ($entry['inletStatusFrequency'] / 10);
        $low_limit = $entry['inletConfigFrequencyLow'];
        $high_limit = $entry['inletConfigFrequencyHigh'];
        discover_sensor($valid['sensor'], 'frequency', $device, $freq_oid, $index, 'ipoman', $descr, $divisor, '1', $low_limit, null, null, $high_limit, $current);
        // FIXME: iPoMan 1201 also says it has 2 inlets, at least until firmware 1.06 - wtf?
    }
}
