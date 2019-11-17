<?php

$version = $device['sysDescr'];
$serial = snmp_get($device, '.1.3.6.1.4.1.890.1.2.2.2.0', '-OQv');
