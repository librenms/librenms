<?php

$temps = snmp_walk($device, '.1.3.6.1.4.1.674.10895.5000.2.6132.1.1.43.1.8.1.5', '-Osqn');
//This will return at least 4 OIDs (multiplied by the number of switches if stacked)  and associated values for various temperatures

$counter = 0;

foreach (explode("\n", $temps) as $i => $t) {
    $t = explode(' ', $t);
    $oid = $t[0];
    $val = $t[1];

    if (substr($oid, -1) == '1') {
        // This code will only pull CPU temp for each stack member, but there is no reason why the additional values couldn't be graphed
        $counter = $counter + 1;
        discover_sensor($valid['sensor'], 'temperature', $device, $oid, $counter, 'dnos', 'Unit ' . $counter . ' CPU temperature', '1', '1', null, null, null, null, $val);
    }
}

// Force10 S-Series
// F10-S-SERIES-CHASSIS-MIB::chStackUnitTemp.1 = Gauge32: 47
// F10-S-SERIES-CHASSIS-MIB::chStackUnitModelID.1 = STRING: S25-01-GE-24V
echo 'FTOS C-Series ';

$oids = snmpwalk_cache_oid($device, 'chStackUnitTemp', [], 'F10-S-SERIES-CHASSIS-MIB', 'ftos');
$oids = snmpwalk_cache_oid($device, 'chStackUnitSysType', $oids, 'F10-S-SERIES-CHASSIS-MIB', 'ftos');

if (is_array($oids)) {
    foreach ($oids as $index => $entry) {
        $descr = 'Unit ' . $index . ' ' . $entry['chStackUnitSysType'];
        $oid = '.1.3.6.1.4.1.6027.3.10.1.2.2.1.14.' . $index;
        $current = $entry['chStackUnitTemp'];
        discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'ftos-sseries', $descr, '1', '1', null, null, null, null, $current);
    }
}
