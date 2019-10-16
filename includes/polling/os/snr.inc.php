<?php

$hardware = trim(snmp_get($device, '.1.3.6.1.4.1.40418.7.100.1.2.0', '-OQv'), '"');
$version = trim(snmp_get($device, '.1.3.6.1.4.1.40418.7.100.1.3.0', '-OQv'), '"');

if (empty($hardware) && empty($version)) {
    $temp_data = snmp_get_multi_oid($device, ['sysHardwareVersion.1', 'sysSoftwareVersion.1'], '-OUQs', 'NAG-MIB');
    $hardware =  $temp_data['sysHardwareVersion.1'];
    $version = $temp_data['sysSoftwareVersion.1'];
    unset($temp_data);
}
