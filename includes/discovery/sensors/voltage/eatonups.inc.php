<?php

echo 'XUPS-MIB ';

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

    discover_sensor(null, 'voltage', $device, $volt_oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
}
