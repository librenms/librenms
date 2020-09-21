<?php

$serial = snmp_get($device, 'productSerialNum.0', '-Ovq', 'NETAPP-MIB');
$hardware = snmp_get($device, 'productModel.0', '-Ovq', 'NETAPP-MIB');
[$version,] = explode(':', snmp_get($device, 'productVersion.0', '-Ovq', 'NETAPP-MIB'));
$version = str_replace('NetApp Release ', '', $version);
