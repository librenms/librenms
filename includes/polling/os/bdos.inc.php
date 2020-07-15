<?php
$version = snmp_get($device, '.1.3.6.1.4.1.16744.2.45.1.2.2.0', '-Ovqs', '');
$serial = snmp_get($device, '.1.3.6.1.4.1.16744.2.45.1.2.13.0', '-Ovqs', '');
$hardware   = snmp_get($device, '.1.3.6.1.4.1.16744.2.45.1.1.1.0', '-OQv', '');