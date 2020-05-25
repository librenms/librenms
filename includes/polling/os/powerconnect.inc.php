<?php

$hardware = 'Dell '.snmp_get($device, 'productIdentificationDisplayName.0', '-Ovq', 'Dell-Vendor-MIB');
$version  = snmp_get($device, 'productIdentificationVersion.0', '-Ovq', 'Dell-Vendor-MIB');
$features = snmp_get($device, 'productIdentificationDescription.0', '-Ovq', 'Dell-Vendor-MIB');

if (strstr($hardware, 'No Such Object available')) {
    $hardware = $device['sysDescr'];
}
