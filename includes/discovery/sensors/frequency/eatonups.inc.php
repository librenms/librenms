<?php

echo 'XUPS-MIB ';

// XUPS-MIB::xupsInputFrequency.0 = INTEGER: 500
$oids = snmpwalk_cache_oid($device, 'xupsInputFrequency', [], 'XUPS-MIB');

foreach ($oids as $freq_id => $data) {
    $freq_oid = ".1.3.6.1.4.1.534.1.3.1.$freq_id";
    $descr = 'Input';
    if (count($oids) > 1) {
        $descr .= " Phase $freq_id";
    }
    $divisor = 10;
    $current = $data['xupsInputFrequency'] / $divisor;
    $type = 'xups';
    $index = '3.1.' . $freq_id;

    discover_sensor($valid['sensor'], 'frequency', $device, $freq_oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
}

// XUPS-MIB::xupsOutputFrequency.0 = INTEGER: 500
$oids = snmpwalk_cache_oid($device, 'xupsOutputFrequency', [], 'XUPS-MIB');

foreach ($oids as $freq_id => $data) {
    $freq_oid = ".1.3.6.1.4.1.534.1.4.2.$freq_id";
    $descr = 'Output';
    if (count($oids) > 1) {
        $descr .= " Phase $freq_id";
    }
    $divisor = 10;
    $current = $data['xupsOutputFrequency'] / $divisor;
    $type = 'xups';
    $index = '4.2.' . $freq_id;
    discover_sensor($valid['sensor'], 'frequency', $device, $freq_oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
}

// XUPS-MIB::xupsBypassFrequency.0 = INTEGER: 500
$oids = snmpwalk_cache_oid($device, 'xupsBypassFrequency', [], 'XUPS-MIB');

foreach ($oids as $freq_id => $data) {
    $freq_oid = ".1.3.6.1.4.1.534.1.5.1.$freq_id";
    $descr = 'Bypass';
    if (count($oids) > 1) {
        $descr .= " Phase $freq_id";
    }
    $divisor = 10;
    $current = $data['xupsBypassFrequency'] / $divisor;
    if ($current != '') {
        // Bypass is not always available in SNMP
        $current /= $divisor;
        $type = 'xups';
        $index = '5.1.' . $freq_id;

        discover_sensor($valid['sensor'], 'frequency', $device, $freq_oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
    }
}
