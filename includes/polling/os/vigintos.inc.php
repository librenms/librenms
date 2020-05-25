<?php
$vigintos_tmp = snmp_get_multi_oid($device, 'host2Version.0', '-OQUs', 'VEL-HOST2-MIB');
$version  = $vigintos_tmp['host2Version.0'];
