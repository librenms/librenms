<?php
$version = snmp_get($device, '.1.3.6.1.4.1.3373.1103.7.2.0', '-Oqv', '', '');
$hardware = snmp_get($device, '.1.3.6.1.4.1.3373.1103.6.2.1.11.1', '-Oqv', '', '');
