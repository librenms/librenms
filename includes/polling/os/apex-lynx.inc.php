<?php
$version = trim(snmp_get($device, ".1.3.6.1.4.1.5454.1.80.1.1.2.0", "-Oqv"), '"');
$hardware = trim(snmp_get($device, ".1.3.6.1.2.1.1.1.0", "-Oqv"), '"');
