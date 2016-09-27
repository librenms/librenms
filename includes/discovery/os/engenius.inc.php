<?php

if (starts_with($sysObjectId, array('.1.3.6.1.4.1.14125.100.1.3', '.1.3.6.1.4.1.14125.101.1.3'))) {
    $os = 'engenius';
} elseif (starts_with($sysDescr, 'Wireless Access Point')) {
    // a little more description about what this does would be nice... ;-)
    $engenius_snmpget = snmp_get($device, 'SNMPv2-SMI::enterprises.14125.2.1.1.6.0', '-Oqv', '');
    if (!empty($engenius_snmpget)) {
        $os = 'engenius';
    }
}
