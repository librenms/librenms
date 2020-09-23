<?php

$version = trim(snmp_get($device, '1.3.6.1.4.1.1588.2.1.1.1.1.6.0', '-Ovq'), '"');
$hardware = trim(snmp_get($device, 'ENTITY-MIB::entPhysicalDescr.1', '-Ovq'), '"');
$serial = trim(snmp_get($device, '1.3.6.1.2.1.47.1.1.1.1.11.1', '-Ovq'), '"');
