<?php

$temp_data = snmp_get_multi_oid($device, ['hikEntity.101.0', 'hikEntityType.0', 'hikEntityIndex.0'], '-OUQs', 'HIKVISION-MIB');

$hostname =
$version  = $temp_data['hikEntity.101.0'];
$hardware = $temp_data['hikEntityType.0'];
$serial   = $temp_data['hikEntityIndex.0'];

unset($temp_data);
