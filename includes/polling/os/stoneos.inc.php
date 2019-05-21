<?php
$hardware = $device['sysDescr'];
$osGet = snmp_get_multi_oid($device, ['sysSoftware.0', 'sysSerialNumber.0'], '-OQUs', 'HILLSTONE-SYSTEM-MIB');
$version = $osGet['sysSoftware.0'];
$serial = $osGet['sysSerialNumber.0'];
