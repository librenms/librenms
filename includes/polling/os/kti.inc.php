<?php

$hardware = trim($device['sysDescr'], '"');
$version = trim(snmp_get($device, '.1.3.6.1.2.1.47.1.1.1.1.9.1', '-Ovq'), '"');
