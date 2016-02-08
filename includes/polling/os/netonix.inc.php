<?php
$version = snmp_get($device, 'firmwareVersion.0', '-OQv', 'NETONIX-SWITCH-MIB', $config['mib_dir'].':'.$config['mib_dir'].'/netonix');
$hardware = $poll_device['sysDescr'];
