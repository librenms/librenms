<?php

$version_oid    = '.1.3.6.1.4.1.16744.2.45.1.2.2.0';
$serial_oid     = '.1.3.6.1.4.1.16744.2.45.1.2.13.0';
$hardware_oid   = '.1.3.6.1.4.1.16744.2.45.1.1.1.0';
$clearpass_data = snmp_get_multi_oid($device, "$version_oid $serial_oid $hardware_oid");

$version  = trim($clearpass_data[$version_oid], '"');
$serial   = trim($clearpass_data[$serial_oid], '"');
$hardware = trim($clearpass_data[$hardware_oid], '"');

unset(
    $clearpass_data,
    $hardware_oid,
    $version_oid
);
