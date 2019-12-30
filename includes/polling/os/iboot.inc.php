<?php
$tmp_iboot = snmp_get_multi($device, ['IBOOTPDU-MIB::firmwareVersion.0', 'IBOOTPDU-MIB::deviceModelName.0'], '-OQUs');

$version  = trim($tmp_iboot[0]['firmwareVersion'], '"');
$hardware = trim($tmp_iboot[0]['deviceModelName'], '"');

unset($tmp_iboot);
