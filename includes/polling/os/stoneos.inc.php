<?php
$hardware = $device['sysDescr'];
$version = snmp_get($device, 'HILLSTONE-SYSTEM-MIB::sysSoftware.0', '-Ovq', 'HILLSTONE-SYSTEM-MIB');
$serial = snmp_get($device, 'HILLSTONE-SYSTEM-MIB::sysSerialNumber.0', '-Ovq', 'HILLSTONE-SYSTEM-MIB');
