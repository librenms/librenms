<?php

$version = snmp_get($device, 'firmwareVersion.0', '-Osqnv', 'NETONIX-SWITCH-MIB', 'mibs:mibs/netonix/');
echo "DEBUG $version DEBUG\n";
