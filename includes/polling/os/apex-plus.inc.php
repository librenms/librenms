<?php
$version = snmp_get($device, '.1.3.6.1.4.1.5454.1.80.1.1.2.0', '-OQv');
$hardware = 'Trango ' . $device['sysDescr'];
