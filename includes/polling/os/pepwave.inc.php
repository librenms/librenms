<?php
$pepwave_tmp = snmp_get_multi_oid($device, ['deviceModel.0', 'deviceSerialNumber.0', 'deviceFirmwareVersion.0'], '-OUQs', 'DEVICE');
$hardware = $pepwave_tmp['deviceModel.0'];
$serial   = $pepwave_tmp['deviceSerialNumber.0'];
$version  = $pepwave_tmp['deviceFirmwareVersion.0'];
