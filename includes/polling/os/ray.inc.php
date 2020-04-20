<?php
$ray_tmp = snmp_get_multi_oid($device, 'productName swVer serialNumber unitType sysLocation sysName', '-OQs', 'RAY-MIB');
$hardware      = $ray_tmp['productName'];
$version       = $ray_tmp['swVer'];
$serial        = $ray_tmp['serialNumber'];
$features      = $ray_tmp['unitType'];
$location      = $ray_tmp['sysLocation'];
$name          = $ray_tmp['sysName'];
unset($ray_tmp);
