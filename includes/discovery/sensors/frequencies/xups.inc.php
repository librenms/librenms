<?php

// XUPS
if ($device['os'] == 'powerware') {
    echo 'XUPS-MIB ';

    // I'm not sure if there is provision for frequency of multiple phases in this MIB -TL
    // XUPS-MIB::xupsInputFrequency.0 = INTEGER: 500
    $freq_oid = '.1.3.6.1.4.1.534.1.3.1.0';
    $descr    = 'Input';
    $divisor  = 10;
    $current  = (snmp_get($device, $freq_oid, '-Oqv') / $divisor);
    $type     = 'xups';
    $index    = '3.1.0';
    discover_sensor($valid['sensor'], 'frequency', $device, $freq_oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);

    // XUPS-MIB::xupsOutputFrequency.0 = INTEGER: 500
    $freq_oid = '1.3.6.1.4.1.534.1.4.2.0';
    $descr    = 'Output';
    $divisor  = 10;
    $current  = (snmp_get($device, $freq_oid, '-Oqv') / $divisor);
    $type     = 'xups';
    $index    = '4.2.0';
    discover_sensor($valid['sensor'], 'frequency', $device, $freq_oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);

    // XUPS-MIB::xupsBypassFrequency.0 = INTEGER: 500
    $freq_oid = '1.3.6.1.4.1.534.1.5.1.0';
    $descr    = 'Bypass';
    $divisor  = 10;
    $current  = snmp_get($device, $freq_oid, '-Oqv');
    if ($current != '') {
        // Bypass is not always available in SNMP
        $current /= $divisor;
        $type     = 'xups';
        $index    = '5.1.0';
        discover_sensor($valid['sensor'], 'frequency', $device, $freq_oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
    }
}//end if
