<?php

use LibreNMS\RRD\RrdDefinition;

$version = trim(snmp_get($device, "MERU-GLOBAL-STATISTICS-MIB::mwSystemGeneralVersion.0", "-OQv"), '"');
$hardware = trim(snmp_get($device, "MERU-GLOBAL-STATISTICS-MIB::mwSystemGeneralModel.0", "-OQv"), '"');
$hostname = trim(snmp_get($device, "MERU-GLOBAL-STATISTICS-MIB::mwSystemGeneralName.0", "-OQv"), '"');
