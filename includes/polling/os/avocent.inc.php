<?php

$avocent_tmp = snmp_get_multi_oid($device, 'pmProductModel.0 pmSerialNumber.0 pmFirmwareVersion.0', '-OUQs', 'PM-MIB');

$hardware = $avocent_tmp['pmProductModel.0'];
$serial   = $avocent_tmp['pmSerialNumber.0'];
$version  = $avocent_tmp['pmFirmwareVersion.0'];

unset($avocent_tmp);
