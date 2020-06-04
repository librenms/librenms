<?php
$fusionhub_tmp = snmp_get_multi_oid($device, ['deviceModel.0', 'deviceSerialNumber.0', 'deviceFirmwareVersion.0'], '-OUQs', 'DEVICE');
$hardware = $fusionhub_tmp['deviceModel.0'];
$serial   = $fusionhub_tmp['deviceSerialNumber.0'];
$version  = $fusionhub_tmp['deviceFirmwareVersion.0'];
