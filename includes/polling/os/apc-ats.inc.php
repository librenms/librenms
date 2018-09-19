<?php

$oids = 'atsIdentSerialNumber.0 atsIdentModelNumber.0 atsIdentFirmwareRev.0';

$data = snmp_get_multi($device, $oids, '-OQUs', 'PowerNet-MIB');
d_echo($data);

$version = $data[0]['atsIdentFirmwareRev'];
$hardware = $data[0]['atsIdentModelNumber'];
$serial = $data[0]['atsIdentSerialNumber'];
