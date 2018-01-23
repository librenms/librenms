<?php


$ray_tmp = snmp_get_multi_oid($device, 'deviceName swVer serialNumber', '-OQs', 'RAY-MIB');
$hardware      = $ray_tmp['deviceName'];
$version       = $ray_tmp['swVer'];
$serial        = $ray_tmp['serialNumber'];
unset($ray_tmp);
