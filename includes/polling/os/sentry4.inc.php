<?php

#Sentry4-MIB::st4UnitModel        "C2S42CE-YCMFAM00"
#Sentry4-MIB::st4SystemFirmwareVersion        "Version 8.0n"
#Sentry4-MIB::st4UnitProductSN        "AAAA0001234"

$hardware = snmp_get($device, "st4UnitModel.1", "-Ovq", "Sentry4-MIB");
$serial = snmp_get($device, "st4UnitProductSN.1", "-Ovq", "Sentry4-MIB");
$version = snmp_get($device, "st4SystemFirmwareVersion.0", "-Ovq", "Sentry4-MIB");
