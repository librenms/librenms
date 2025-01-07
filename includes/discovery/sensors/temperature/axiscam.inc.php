<?php

echo 'AXIS Temperatures ';

// Set Temp Limits based on Device Description
switch (true) {
    case stristr($device['sysDescr'], 'P1425-LE'):
        $high_limit = 50;
        $high_warn_limit = 40;
        $low_warn_limit = -20;
        $low_limit = -30;
        break;
    case stristr($device['sysDescr'], 'P1435-LE'):
        $high_limit = 60;
        $high_warn_limit = 50;
        $low_warn_limit = -20;
        $low_limit = -30;
        break;
    case stristr($device['sysDescr'], 'P1455-LE'):
        $high_limit = 60;
        $high_warn_limit = 50;
        $low_warn_limit = -30;
        $low_limit = -40;
        break;
    case stristr($device['sysDescr'], 'P5676-LE'):
        $high_limit = 50;
        $high_warn_limit = 40;
        $low_warn_limit = -20;
        $low_limit = -30;
        break;
    default:
        $high_limit = 5;
        $high_warn_limit = 10;
        $low_warn_limit = 30;
        $low_limit = 35;
}

// Temp Sensor Status
$oids_tmp = snmpwalk_cache_multi_oid($device, 'tempSensorTable', [], 'AXIS-VIDEO-MIB');
$cur_oid = '.1.3.6.1.4.1.368.4.1.3.1.4.1.';

$oids = [];
// Exclude from $oids content .common string
foreach ($oids_tmp as $key_oids_tmp => $val_oids_tmp) {
    $oids[str_replace('common.', '', $key_oids_tmp)] = $val_oids_tmp;
}

foreach (array_keys($oids) as $index) {
    $current = $oids[$index]['tempSensorValue'];
    $oid = $cur_oid . $index;

    discover_sensor(null, 'temperature', $device, $oid, $index, 'axiscam', 'Temperature Sensor ' . $index, '1', '1', $low_limit, $low_warn_limit, $high_warn_limit, $high_limit, $current);
}
