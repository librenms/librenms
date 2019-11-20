<?php

$avocent_tmp = snmp_get_multi_oid($device, ['pmProductModel.0', 'pmSerialNumber.0', 'pmFirmwareVersion.0'], '-OUQs', 'PM-MIB');

$hardware = $avocent_tmp['pmProductModel.0'];
$serial   = $avocent_tmp['pmSerialNumber.0'];
$version  = $avocent_tmp['pmFirmwareVersion.0'];

if (empty($hardware)) {
    if (starts_with($device['sysObjectID'], '.1.3.6.1.4.1.10418.16')) {
        $avocent_oid = '.1.3.6.1.4.1.10418.16.2.1';
    } elseif (starts_with($device['sysObjectID'], '.1.3.6.1.4.1.10418.26')) {
        $avocent_oid = '.1.3.6.1.4.1.10418.26.2.1';
    }
    if ($avocent_oid) {
        $avocent_tmp = snmp_get_multi_oid($device, "$avocent_oid.2.0 $avocent_oid.4.0 $avocent_oid.7.0");
        list($hardware,) = explode(' ', $avocent_tmp["$avocent_oid.2.0"], 2);
        $serial   = $avocent_tmp["$avocent_oid.4.0"];
        $version  = $avocent_tmp["$avocent_oid.7.0"];
    }
}

unset($avocent_tmp);
