<?php

// Device might not have a card 1 (or even card2 if it is an E7-20)
$version = strtok(snmp_walk($device, "e7CardSoftwareVersion.1", "-OQv", "E7-Calix-MIB"), PHP_EOL);
$hardware = "Calix " . $device['sysDescr'];
$features = str_replace(PHP_EOL, ', ', snmp_walk($device, "e7CardProvType", "-OQv", "E7-Calix-MIB"));
$serial = str_replace(PHP_EOL, ', ', snmp_walk($device, "e7CardSerialNumber", "-OQv", "E7-Calix-MIB"));
