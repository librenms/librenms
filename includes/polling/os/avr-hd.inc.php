<?php

$hardware = trim(snmp_get($device, '.1.3.6.1.4.1.28194.1.37.0', '-OQv'), '" ');

if (preg_match('/avr-hd \[(.*?)\]/', $device['sysDescr'], $regexp_result)) {
    $version  = $regexp_result[1];
}
