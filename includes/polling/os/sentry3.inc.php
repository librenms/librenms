<?php

#Sentry3-MIB::towerModelNumber        "CW-24V2-L30M"
#Sentry3-MIB::systemVersion        "Sentry Switched CDU Version 6.0g"
#Sentry3-MIB::towerProductSN        "ABEF0001561"

$hardware = snmp_get($device, "towerModelNumber.1", "-Ovq", "Sentry3-MIB");
$serial = snmp_get($device, "towerProductSN.1", "-Ovq", "Sentry3-MIB");
$version = snmp_get($device, "systemVersion.0", "-Ovq", "Sentry3-MIB");
$version = preg_split('/Sentry\ (Switched|Smart)\ CDU\ Version/', $version);
$version = $version[1];
