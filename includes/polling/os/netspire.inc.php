<?php

$oids = array (
    'hardware' => 'netspireDeviceModelName.0',
    'serial'   => 'netSpireDeviceDeviceSerialNo.0'
);

$data = snmp_get_multi_oid($device, $oids, '-OUQs', 'OACOMMON-MIB');

foreach ($oids as $var => $oid) {
    $$var = $data[$oid];
}

unset($data, $oids);
