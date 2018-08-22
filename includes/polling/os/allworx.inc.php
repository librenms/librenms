<?php
$hardware = $device['sysDescr'];
$version = snmp_get($device, '1.3.6.1.2.1.27.1.1.4.1', '-OQv', '', '');
$serial = snmp_get($device, '1.3.6.1.2.1.1.5.0', '-OQv', '', '');
