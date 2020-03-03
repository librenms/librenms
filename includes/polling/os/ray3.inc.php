<?php
$ray_tmp = snmp_get_multi_oid($device, 'productName.0 swVer.0 serialNumber.0 unitType.0', '-OQs', 'RAY3-MIB');
$hardware      = $ray_tmp['productName.0'];
$version       = $ray_tmp['swVer.0'];
$serial        = $ray_tmp['serialNumber.0'];
$features      = $ray_tmp['unitType.0'];
unset($ray_tmp);
