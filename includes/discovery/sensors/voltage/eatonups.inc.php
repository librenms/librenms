<?php

echo 'XUPS-MIB ';

// XUPS-MIB::xupsBatVoltage.0 = INTEGER: 51
$oids = snmpwalk_cache_oid($device, 'xupsBatVoltage', [], 'XUPS-MIB');

foreach ($oids as $volt_id => $data) {
    $volt_oid = ".1.3.6.1.4.1.534.1.2.2.$volt_id";
    $divisor = 1;
    $volt = $data['xupsBatVoltage'] / $divisor;
    $descr = 'Battery' . (count($oids) == 1 ? '' : ' ' . ($volt_id + 1));
    $type = 'xups';
    $index = '1.2.5.' . $volt_id;

    discover_sensor($valid['sensor'], 'voltage', $device, $volt_oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $volt);
}

// XUPS-MIB::xupsInputVoltage.1 = INTEGER: 228
$oids = snmpwalk_cache_oid($device, 'xupsInputVoltage', [], 'XUPS-MIB');

foreach ($oids as $volt_id => $data) {
    $volt_oid = ".1.3.6.1.4.1.534.1.3.4.1.2.$volt_id";
    $descr = 'Input';
    if (count($oids) > 1) {
        $descr .= " Phase $volt_id";
    }
    $type = 'xups';
    $divisor = 1;
    $current = $data['xupsInputVoltage'] / $divisor;
    $index = '3.4.1.2.' . $volt_id;

    discover_sensor($valid['sensor'], 'voltage', $device, $volt_oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
}

// XUPS-MIB::xupsOutputVoltage.1 = INTEGER: 228
$oids = snmpwalk_cache_oid($device, 'xupsOutputVoltage', [], 'XUPS-MIB');

foreach ($oids as $volt_id => $data) {
    $volt_oid = ".1.3.6.1.4.1.534.1.4.4.1.2.$volt_id";
    $descr = 'Output';
    if (count($oids) > 1) {
        $descr .= " Phase $volt_id";
    }

    $type = 'xups';
    $divisor = 1;
    $current = $data['xupsOutputVoltage'] / $divisor;
    $index = '4.4.1.2.' . $volt_id;

    discover_sensor($valid['sensor'], 'voltage', $device, $volt_oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
}

// XUPS-MIB::xupsBypassNumPhases.0 = INTEGER: 1
$oids = snmpwalk_cache_oid($device, 'xupsBypassVoltage', [], 'XUPS-MIB');

foreach ($oids as $volt_id => $data) {
    $volt_oid = ".1.3.6.1.4.1.534.1.5.3.1.2.$volt_id";
    $descr = 'Bypass';
    if (count($oids) > 1) {
        $descr .= " Phase $volt_id";
    }

    $type = 'xups';
    $divisor = 1;
    $current = $data['xupsBypassVoltage'] / $divisor;
    $index = '5.3.1.2.' . $volt_id;

    discover_sensor($valid['sensor'], 'voltage', $device, $volt_oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
}
