<?php


$hardware = snmp_get($device, "upsIdentModel.0", "-OQv", "UPS-MIB");
$version = snmp_get($device, "upsIdentAgentSoftwareVersion.0", "-OQv", "UPS-MIB");
