<?php

$hardware = snmp_get($device, "fusionInfoBoard", "-Ovq", "EXALINK-FUSION-MIB");
$serial = snmp_get($device, "fusionInfoSerial", "-Ovq", "EXALINK-FUSION-MIB");
$version = snmp_get($device, "fusionInfoVersion", "-Ovq", "EXALINK-FUSION-MIB") . " ";
$version .= snmp_get($device, "fusionInfoSoftware", "-Ovq", "EXALINK-FUSION-MIB");
