<?php

echo 'RFC1628 ';

$oids = trim(snmp_walk($device, '.1.3.6.1.2.1.33.1.3.2.0', '-OsqnU'));
d_echo($oids."\n");

list($unused,$numPhase) = explode(' ', $oids);
for ($i = 1; $i <= $numPhase; $i++) {
    $freq_oid  = ".1.3.6.1.2.1.33.1.3.3.1.2.$i";
    $descr = 'Input';
    if ($numPhase > 1) {
        $descr .= " Phase $i";
    }
    $divisor = get_device_divisor($device, $pre_cache['poweralert_serial'], 'frequency', $freq_oid);
    $current = (snmp_get($device, $freq_oid, '-Oqv') / $divisor);
    $type    = 'rfc1628';

    $index = '3.2.0.'.$i;
    discover_sensor($valid['sensor'], 'frequency', $device, $freq_oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current);
}

$freq_oid = '.1.3.6.1.2.1.33.1.4.2.0';
$current_freq  = snmp_get($device, $freq_oid, '-Oqv');
if (is_numeric($current_freq)) {
    $descr    = 'Output';
    $divisor = get_device_divisor($device, $pre_cache['poweralert_serial'], 'frequency', $freq_oid);
    $type     = 'rfc1628';
    $index = '4.2.0';

    discover_sensor($valid['sensor'], 'frequency', $device, $freq_oid, $index, $type, $descr, $divisor, 1, null, null, null, null, $current_freq / $divisor);
}

$freq_oid = '.1.3.6.1.2.1.33.1.5.1.0';
$current_freq  = snmp_get($device, $freq_oid, '-Oqv');
if (is_numeric($current_freq)) {
    $descr    = 'Bypass';
    $divisor = get_device_divisor($device, $pre_cache['poweralert_serial'], 'frequency', $freq_oid);
    $type     = 'rfc1628';
    $index = '5.1.0';

    discover_sensor($valid['sensor'], 'frequency', $device, $freq_oid, $index, $type, $descr, $divisor, 1, null, null, null, null, $current_freq / $divisor);
}
