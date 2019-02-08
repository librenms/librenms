<?php

$hardware = 'DKT ' . snmp_get($device, 'DKT-GENERIC-MIB::hwVersion.0', '-OQv');
$version = snmp_get($device, 'DKT-GENERIC-MIB::swVersion.0', '-OQv');
