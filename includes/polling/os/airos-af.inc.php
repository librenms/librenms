<?php

$hardware = "Ubiquiti AF ".trim(snmp_get($device, "dot11manufacturerProductName.5", "-Ovq", "IEEE802dot11-MIB", "/opt/librenms/mibs:/opt/librenms/mibs/rfc:/opt/librenms/mibs/net-snmp:/opt/librenms/mibs/ubiquiti"));

$version  = trim(snmp_get($device, "dot11manufacturerProductVersion.5", "-Ovq", "IEEE802dot11-MIB", "/opt/librenms/mibs:/opt/librenms/mibs/rfc:/opt/librenms/mibs/net-snmp:/opt/librenms/mibs/ubiquiti"));
list(, $version) = preg_split('/\.v/',$version);

// EOF
