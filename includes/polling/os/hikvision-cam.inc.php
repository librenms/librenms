<?php

$temp_data = snmp_get_multi_oid($device, ['softwVersion.0', 'deviceType.0', 'videoEncode.0', 'videoNetTrans.0'], '-OUQs', 'HIK-DEVICE-MIB');

$version   = $temp_data['softwVersion.0'];
$hardware  = $temp_data['deviceType.0'];
$features  = $temp_data['videoEncode.0'];
$features .= '-' . $temp_data['videoNetTrans.0'];

unset($temp_data);
