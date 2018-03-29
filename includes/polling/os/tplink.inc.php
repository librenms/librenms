<?php

$version  = trim(snmp_get($device, 'RMON-MIB::rmon.19.2.0', '-Ovq'), '"');
$hardware = trim(snmp_get($device, 'RMON-MIB::rmon.19.3.0', '-Ovq'), '"');
