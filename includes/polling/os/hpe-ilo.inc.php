<?php

$data = snmp_get_multi($device, ['cpqSiProductName.0', 'cpqSiSysSerialNum.0', 'cpqHoFwVerVersion.0'], '-OQUs', 'CPQSINFO-MIB:CPQHOST-MIB');
$hardware = trim($data[0]['cpqSiProductName'], '"');
$serial = trim($data[0]['cpqSiSysSerialNum'], '"');
$version  = stristr($data[0]['cpqHoFwVerVersion'], ' ', true);
unset($data);
