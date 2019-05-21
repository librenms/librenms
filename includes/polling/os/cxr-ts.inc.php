<?php

$oids = ['productName.0', 'version.0'];

$data = snmp_get_multi($device, $oids, '-OQUs', 'CXR-TS-MIB');

//d_echo($data);

$hardware = $data[0]['productName'];
$version  = $data[0]['version'];
