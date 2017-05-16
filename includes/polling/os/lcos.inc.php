<?php
$lcos_data = snmp_get_multi_oid($device, 'lcsFirmwareVersionTableEntrySerialNumber.1 lcsFirmwareVersionTableEntryVersion.1  lcsFirmwareVersionTableEntryModule.1', '-OQs', 'LCOS-MIB');

$serial  = $lcos_data['lcsFirmwareVersionTableEntrySerialNumber.eIfc'];
$version = $lcos_data['lcsFirmwareVersionTableEntryVersion.eIfc'];
$hardware = $lcos_data['lcsFirmwareVersionTableEntryModule.eIfc'];

unset($lcos_data);
