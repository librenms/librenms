<?php

$hardware = snmp_get($device, '.1.3.6.1.4.1.17420.1.2.9.1.19.0', '-Ovq');
$hardware = str_replace('"', '', $hardware);
$version = snmp_get($device, '.1.3.6.1.4.1.17420.1.2.4.0', '-Ovq');
$version = str_replace('"', '', $version);
