<?php

$r_temp = snmp_get_multi_oid($device, ['.1.3.6.1.4.1.8886.6.1.1.1.1.0', '.1.3.6.1.4.1.8886.6.1.1.1.2.0', '.1.3.6.1.4.1.8886.6.1.1.1.14.0'], '-OQUn');
$version   = $r_temp['.1.3.6.1.4.1.8886.6.1.1.1.1.0'];
$hardware  = $r_temp['.1.3.6.1.4.1.8886.6.1.1.1.2.0'];
$serial    = $r_temp['.1.3.6.1.4.1.8886.6.1.1.1.14.0'];

if (empty($version) && (preg_match('/^ROAP  Version ([^,]+)/', $device['sysDescr'], $regexp_result))) {
    $version = $regexp_result[1];
}
