<?php

$oids = SnmpQuery::walk('lmFanSensorsTable');
$oids = $oids->table(1);

if (! empty($oids)) {
    echo 'LM-SENSORS ';
}

foreach ($oids as $index => $data) {
    $oid = '.1.3.6.1.4.1.2021.13.16.3.1.3.' . $index;
    $current = $data["LM-SENSORS-MIB::lmFanSensorsValue"];
    $descr = trim(str_ireplace('fan-', '', $data["LM-SENSORS-MIB::lmFanSensorsDevice"]));
    if ($current !== false && $current >= 0) {
        discover_sensor($valid['sensor'], 'fanspeed', $device, $oid, $index, 'lmsensors', $descr, '1', '1', null, null, null, null, $current);
    }
}
