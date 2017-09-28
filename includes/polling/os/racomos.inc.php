<?php

$hardware = trim(snmp_get($device, '.1.3.6.1.2.1.1.5', '-OQv', '', ''), '"');
$version  = trim(snmp_get($device, '.1.3.6.1.4.1.33555.1.1.2.2', '-OQv', '', ''), '"');
$serial   = trim(snmp_get($device, '.1.3.6.1.4.1.33555.1.1.1.2', '-OQv', '', ''), '"');
