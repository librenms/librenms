<?php

echo ' IPOMANII-MIB ';

$oids = [];

d_echo('inletConfigVoltageHigh ');
$oids = snmpwalk_cache_multi_oid($device, 'inletConfigVoltageHigh', $oids, 'IPOMANII-MIB');
d_echo('inletConfigVoltageLow ');
$oids = snmpwalk_cache_multi_oid($device, 'inletConfigVoltageLow', $oids, 'IPOMANII-MIB');
d_echo('inletStatusVoltage ');
$oids = snmpwalk_cache_multi_oid($device, 'inletStatusVoltage', $oids, 'IPOMANII-MIB');

if (is_array($oids)) {
    foreach ($oids as $index => $entry) {
        $volt_oid = '.1.3.6.1.4.1.2468.1.4.2.1.3.1.3.1.2.' . $index;
        $divisor = 10;
        $descr = (trim($pre_cache['ipoman']['in'][$index]['inletConfigDesc'], '"') != '' ? trim($pre_cache['ipoman']['in'][$index]['inletConfigDesc'], '"') : "Inlet $index");
        $current = ($entry['inletStatusVoltage'] / 10);
        $low_limit = $entry['inletConfigVoltageLow'];
        $high_limit = $entry['inletConfigVoltageHigh'];

        discover_sensor($valid['sensor'], 'voltage', $device, $volt_oid, $index, 'ipoman', $descr, $divisor, '1', $low_limit, null, null, $high_limit, $current);
        // FIXME: iPoMan 1201 also says it has 2 inlets, at least until firmware 1.06 - wtf?
    }
}
