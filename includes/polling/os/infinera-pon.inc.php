<?php
// Get version number as guessed with the provided snmpwalk. No mib so far
$version = snmp_get($device, '.1.3.6.1.4.1.42229.1801.3.4.0', '-OQv');
