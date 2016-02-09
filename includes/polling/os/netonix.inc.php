<?php
$version = snmp_get($device, 'firmwareVersion.0', '-OQv', 'NETONIX-SWITCH-MIB', $config['mibdir'].':'.$config['mibdir'].'/netonix');
$hardware = $poll_device['sysDescr'];
