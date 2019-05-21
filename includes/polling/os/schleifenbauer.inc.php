<?php

$master_unit = snmp_get($device, ".1.3.6.1.4.1.31034.12.1.1.1.2.4.1.2.1", '-Oqv', '');
$hardware    = snmp_get($device, ".1.3.6.1.4.1.31034.12.1.1.2.1.1.1.5.$master_unit", '-Oqv', '');
$firmware    = snmp_get($device, ".1.3.6.1.4.1.31034.12.1.1.2.1.1.1.2.$master_unit", '-Oqv', '');
$buildnumber = snmp_get($device, ".1.3.6.1.4.1.31034.12.1.1.2.1.1.1.3.$master_unit", '-Oqv', '');
$serial      = snmp_get($device, ".1.3.6.1.4.1.31034.12.1.1.2.1.1.1.6.$master_unit", '-Oqv', '');
$version     = "- version $firmware, build $buildnumber";
