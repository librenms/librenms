<?php

$temp_data  = snmp_get_multi_oid($device, ['facSysSerial.0', 'facSysModel.0', 'facSysVersion.0'], '-OUQs', 'FORTINET-FORTIAUTHENTICATOR-MIB');
$temp_version = explode(' ', $temp_data['facSysVersion.0']);

$hardware        = $temp_data['facSysModel.0'];
$serial          = $temp_data['facSysSerial.0'];
$version         = $temp_version[1];

unset($temp_data, $temp_version);
