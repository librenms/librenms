<?php

use LibreNMS\RRD\RrdDefinition;

$hardware = trim(snmp_get($device, '.1.3.6.1.4.1.11256.1.0.1.0', '-Osqv'), '"');
$version = trim(snmp_get($device, '.1.3.6.1.4.1.11256.1.0.2.0', '-Osqv'), '"');
$serial = trim(snmp_get($device, '.1.3.6.1.4.1.11256.1.0.3.0', '-Osqv'), '"');
$sysName = trim(snmp_get($device, '.1.3.6.1.4.1.11256.1.0.4.0', '-Osqv'), '"');
