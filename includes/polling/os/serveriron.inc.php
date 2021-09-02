<?php

$version = trim(snmp_get($device, '.1.3.6.1.4.1.1991.1.1.2.1.11.0', '-Ovq'), '"');
$hardware = trim(snmp_get($device, '.1.3.6.1.4.1.1991.1.1.2.2.1.1.2.1', '-Ovq'), '"');
$serial = trim(snmp_get($device, '.1.3.6.1.4.1.1991.1.1.1.1.2.0', '-Ovq'), '"');
