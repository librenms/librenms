<?php

$hardware = $poll_device['sysDescr'];
$version = explode("ITS", trim(snmp_get($device, '.1.3.6.1.4.1.890.1.15.3.1.6.0', '-Osqv'), '"'), 2);
$version = $version[0];
$serial = trim(snmp_get($device, '.1.3.6.1.4.1.890.1.15.3.1.12.0', '-Oqv'), '"');
