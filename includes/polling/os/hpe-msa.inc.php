<?php

$oids = ['cpqSiProductName.0', 'cpqSiSysSerialNum.0'];

$data = snmp_get_multi($device, $oids, '-OQUs', 'CPQSINFO-MIB');

$hardware = str_replace('"', '', $data[0]['cpqSiProductName']);
$serial = str_replace('"', '', $data[0]['cpqSiSysSerialNum']);
