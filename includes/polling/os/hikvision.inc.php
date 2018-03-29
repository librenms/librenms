<?php

$version  = trim(snmp_get($device, '.1.3.6.1.4.1.39165.1.3.0', '-OQv', '', ''), '"');
$hardware = trim(snmp_get($device, '.1.3.6.1.4.1.39165.1.1.0', '-OQv', '', ''), '"');
