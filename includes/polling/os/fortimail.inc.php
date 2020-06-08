<?php

$temp_data = snmp_get_multi_oid($device, 'fmlSysModel.0 fmlSysVersion.0 fmlSysSerial.0', '-OUQs', 'FORTINET-FORTIMAIL-MIB');

$hardware = $temp_data['fmlSysModel.0'];
$version = $temp_data['fmlSysVersion.0'];
$serial = $temp_data['fmlSysSerial.0'];

unset($temp_data);
