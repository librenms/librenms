<?php

// RFC1628 UPS
if (isset($config['modules_compat']['rfc1628'][$device['os']]) && $config['modules_compat']['rfc1628'][$device['os']]) {
    echo 'RFC1628 ';

    $oids = trim(snmp_walk($device, '1.3.6.1.2.1.33.1.3.2.0', '-OsqnU'));
    d_echo($oids."\n");

    list($unused,$numPhase) = explode(' ', $oids);
    for ($i = 1; $i <= $numPhase; $i++) {
        $freq_oid  = "1.3.6.1.2.1.33.1.3.3.1.2.$i";
        $descr = 'Input';
        if ($numPhase > 1) {
            $descr .= " Phase $i";
        }

        $current = (snmp_get($device, $freq_oid, '-Oqv') / 10);
        $type    = 'rfc1628';
        $divisor = 10;
        if ($device['os'] == 'huaweiups') {
            $divisor = 100;
        };
        $index = '3.2.0.'.$i;
        discover_sensor($valid['sensor'], 'frequency', $device, $freq_oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
    }

    $freq_oid = '1.3.6.1.2.1.33.1.4.2.0';
    $descr    = 'Output';
    $current  = (snmp_get($device, $freq_oid, '-Oqv') / 10);
    $type     = 'rfc1628';
    $divisor  = 10;
    if ($device['os'] == 'huaweiups') {
        $divisor = 100;
    };
    $index = '4.2.0';
    discover_sensor($valid['sensor'], 'frequency', $device, $freq_oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);

    $freq_oid = '1.3.6.1.2.1.33.1.5.1.0';
    $descr    = 'Bypass';
    $current  = (snmp_get($device, $freq_oid, '-Oqv') / 10);
    $type     = 'rfc1628';
    $divisor  = 10;
    if ($device['os'] == 'huaweiups') {
        $divisor = 100;
    };
    $index = '5.1.0';
    discover_sensor($valid['sensor'], 'frequency', $device, $freq_oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
}//end if
