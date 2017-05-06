<?php

$version = trim(snmp_get($device, ".1.3.6.1.4.1.30631.1.9.4.1.0", "-Oqv"), '"');
$serial  = trim(snmp_get($device, ".1.3.6.1.4.1.30631.1.9.4.2.0", "-Oqv"), '"');
