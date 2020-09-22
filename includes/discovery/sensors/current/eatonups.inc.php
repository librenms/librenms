<?php

echo 'XUPS-MIB ';

$oids = snmpwalk_cache_oid($device, 'xupsBatCurrent', [], 'XUPS-MIB');

foreach ($oids as $current_id => $data) {
    $current_oid = ".1.3.6.1.4.1.534.1.2.3.$current_id";
    $divisor = 1;
    $current = $data['xupsBatCurrent'];
    $descr = 'Battery' . (count($oids) == 1 ? '' : ' ' . ($current_id + 1));
    $type = 'xups';
    $index = '1.2.3.' . $current_id;

    discover_sensor($valid['sensor'], 'current', $device, $current_oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
}

$oids = snmpwalk_cache_oid($device, 'xupsOutputCurrent', [], 'XUPS-MIB');

foreach ($oids as $current_id => $data) {
    $current_oid = ".1.3.6.1.4.1.534.1.4.4.1.3.$current_id";
    $descr = 'Output';
    if (count($oids) > 1) {
        $descr .= " Phase $current_id";
    }
    $current = $data['xupsOutputCurrent'];
    $type = 'xups';
    $divisor = 1;
    $index = '4.4.1.3.' . $current_id;

    discover_sensor($valid['sensor'], 'current', $device, $current_oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
}

$oids = snmpwalk_cache_oid($device, 'xupsInputCurrent', [], 'XUPS-MIB');

foreach ($oids as $current_id => $data) {
    $current_oid = ".1.3.6.1.4.1.534.1.3.4.1.3.$current_id";
    $descr = 'Input';
    if (count($oids) > 1) {
        $descr .= " Phase $current_id";
    }
    $current = $data['xupsInputCurrent'];
    $type = 'xups';
    $divisor = 1;
    $index = '3.4.1.3.' . $current_id;

    discover_sensor($valid['sensor'], 'current', $device, $current_oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
}
