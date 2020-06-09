<?php

$hardware = $device['sysDescr'];
$data = snmp_get_multi($device, ['sysSwVersionString.0', 'sysProductSerialNumber.0'], '-OQU', 'ZYXEL-ES-COMMON');
$version = $data[0]['ZYXEL-ES-COMMON::sysSwVersionString'];
$serial = $data[0]['ZYXEL-ES-COMMON::sysProductSerialNumber'];
$features = snmp_get($device, 'operationMode.0', '-Ovq', 'ZYXEL-ES-ZyxelAPMgmt');
