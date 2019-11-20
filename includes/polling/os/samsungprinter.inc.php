<?php
// private::enterprises.236.11.5.11.53.31.1.4.0 = STRING: "CLX-3170 Series"
$hardware = trim(snmp_get($device, '1.3.6.1.4.1.236.11.5.11.53.31.1.4.0', '-OQv', '', ''), '" ');
// mgnt::mib-2.43.5.1.1.17.1 = STRING: "QUV960066FJ124"
$serial = trim(snmp_get($device, '1.3.6.1.2.1.43.5.1.1.17.1', '-OQv', '', ''), '" ');
// mgnt::mib-2.1.1 = STRING: "Samsung CLX-3170 Series;V1.00.01.64 Sep-27-2010;Engine 1.77.81;NIC V4.01.20(CLX-3170) 02-05-2010;S/N JFJIUTM748HJGK983"
$osin = trim($device['sysDescr'], '" ');
//$osin = "Samsung CLX-3170 Series;V1.00.01.64 Sep-27-2010;Engine 1.77.81;NIC V4.01.20(CLX-3170) 02-05-2010;S/N JFJIUTM748HJGK983";
$osar = preg_split('/;S\/N [A-Z0-9]+/', $osin, -1);
$os = array_shift($osar);
//print_r($osar);
//echo $os;
