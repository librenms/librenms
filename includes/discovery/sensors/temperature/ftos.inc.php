<?php

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

$oids = snmpwalk_cache_oid($device, 'chSysCardTemp', [], 'F10-C-SERIES-CHASSIS-MIB', 'ftos');
if (is_array($oids)) {
    foreach ($oids as $index => $entry) {
        $entry['descr'] = 'Slot ' . $index;
        $entry['oid'] = '.1.3.6.1.4.1.6027.3.8.1.2.1.1.5.' . $index;
        $entry['current'] = $entry['chSysCardTemp'];
        discover_sensor($valid['sensor'], 'temperature', $device, $entry['oid'], $index, 'ftos-cseries', $entry['descr'], '1', '1', null, null, null, null, $entry['current']);
    }
}

echo 'FTOS E-Series ';

$oids = snmpwalk_cache_oid($device, 'chSysCardUpperTemp', [], 'F10-CHASSIS-MIB', 'ftos');

if (is_array($oids)) {
    foreach ($oids as $index => $entry) {
        $descr = 'Slot ' . $index;
        $oid = '.1.3.6.1.4.1.6027.3.1.1.2.3.1.8.' . $index;
        $current = $entry['chSysCardUpperTemp'];

        discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'ftos-eseries', $descr, '1', '1', null, null, null, null, $current);
    }
}
