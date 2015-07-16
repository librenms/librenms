<?php

$version  = trim(snmp_get($device, '.1.3.6.1.4.1.674.10892.2.1.2.1.0', '-OQv'), '"');
$hardware = trim(snmp_get($device, '.1.3.6.1.4.1.674.10892.2.1.1.2.0', '-OQv'), '"');
$serial   = trim(snmp_get($device, '.1.3.6.1.4.1.674.10892.2.1.1.11.0', '-OQv'), '"');
