<?php
$version = trim(snmp_get($device, ".1.3.6.1.2.1.47.1.1.1.1.8.1", "-Oqv"), '"');
$hardware = trim(snmp_get($device, ".1.3.6.1.2.1.47.1.1.1.1.9.1", "-Oqv"), '"');
