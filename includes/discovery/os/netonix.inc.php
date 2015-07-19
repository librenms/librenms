<?php

$version = snmp_get($device, 'firmwareVersion.0', '-Osqnv', 'NETONIX-SWITCH-MIB', 'mibs:mibs/netonix/');
list(,$version) = explode(': ', $version);
if (is_numeric($version)) {
    $os = 'netonix';
}
