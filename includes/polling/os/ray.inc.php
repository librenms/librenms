<?php
$ray_tmp = snmp_get_multi_oid($device, 'productName swVer serialNumber unitType', '-OQs', 'RAY-MIB');
$hardware      = $ray_tmp['productName'];
$version       = $ray_tmp['swVer'];
$serial        = $ray_tmp['serialNumber'];
$features      = $ray_tmp['unitType'];
unset($ray_tmp);
