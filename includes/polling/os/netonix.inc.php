<?php
$version = snmp_get($device, 'firmwareVersion.0', '-OQv', 'NETONIX-SWITCH-MIB', '/opt/librenms/mibs:/opt/librenms/mibs/netonix');
$hardware = $poll_device['sysDescr'];
