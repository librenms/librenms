<?php

echo 'AXIS Temperatures ';

// Temp Sensor Status
$oids_tmp = snmpwalk_cache_multi_oid($device, 'tempSensorTable', [], 'AXIS-VIDEO-MIB');
$cur_oid = '.1.3.6.1.4.1.368.4.1.3.1.4.1.';

// Exclude from $oids content .common string
foreach ($oids_tmp as $key_oids_tmp => $val_oids_tmp) {
    $oids[str_replace('common.', '', $key_oids_tmp)] = $val_oids_tmp;
}

foreach (array_keys($oids) as $index) {
    $current = $cur_oid[$index]['tempSensorValue'];
    $oid = $cur_oid . $index;

    discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'axiscam', 'Temperature Sensor ' . $index, '1', '1', '5', '10', '30', '35', $current);
}
