<?php

$oids = ['cmcIIIUnitFWRev.0', 'cmcIIIDevName.1', 'cmcIIIUnitSerial.0'];

$data = snmp_get_multi($device, $oids, '-OQUs', 'RITTAL-CMC-III-MIB');
d_echo($data);

$version = $data[0]['cmcIIIUnitFWRev'];
$hardware = $data[1]['cmcIIIDevName'];
$serial = $data[0]['cmcIIIUnitSerial'];
