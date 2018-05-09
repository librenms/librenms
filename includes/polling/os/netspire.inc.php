<?php

$oids = array('netspireDeviceModelName.0', 'netSpireDeviceDeviceSerialNo.0');

$data = snmp_get_multi_oid($device, $oids, '-OUQs', 'OACOMMON-MIB', 'openaccess');

$hardware = $data[$oids[0]];
$serial = $data[$oids[1]];

unset($data);
unset($oids);
