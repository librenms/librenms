<?php

$oids = 'dlMCS ulMCS eNBID pCID softwareVersion modelName hardwareVersion SN bootROM lteBand';
$data = snmp_getnext_multi($device, $oids, '-OUQs', 'ZMTEL-ODU-MIB');

$version  = $data['softwareVersion'];
$hardware = $data['modelName'] . ' ' . $data['hardwareVersion'];
$serial   = $data['SN'];
$features = 'eNodeB: ' . $data['eNBID'] . ' Physical Cell: ' . $data['pCID'] . ' DL Modulation: ' . $data['dlMCS'] . ' UL Modulation: ' . $data['ulMCS'] . ' bootROM: ' . $data['bootROM'] . ' LTE Band: ' . $data['lteBand'];
unset($oids, $data);
