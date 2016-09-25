<?php

if (starts_with($sysDescr, 'CS121') && str_contains(snmp_get($device, 'UPS-MIB::upsIdentManufacturer.0', '-Oqv', ''), array('Multimatic', 'S2S'))) {
    $os = 'multimatic';
}
