<?php

$hardware = snmp_get($device, '.1.3.6.1.4.1.3808.1.1.3.1.5.0', '-Ovq');
$hardware = str_replace('"', '', $hardware);
$version = snmp_get($device, '.1.3.6.1.4.1.3808.1.1.3.1.3.0', '-Ovq');
$version = str_replace('"', '', $version);
