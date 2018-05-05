<?php

$version = snmp_get($device, "MERU-GLOBAL-STATISTICS-MIB::mwSystemGeneralVersion.0", "-OQv");
$hardware = snmp_get($device, "MERU-GLOBAL-STATISTICS-MIB::mwSystemGeneralModel.0", "-OQv");
