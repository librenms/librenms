<?php
$version = trim(snmp_get($device, '.1.3.6.1.4.1.3375.2.1.4.2.0', '-OQv'), '"');
$hardware = trim(snmp_get($device, '.1.3.6.1.4.1.3375.2.1.3.5.2.0', '-OQv'), '"');
$serial = trim(snmp_get($device, '.1.3.6.1.4.1.3375.2.1.3.3.3.0', '-OQv'), '"');
