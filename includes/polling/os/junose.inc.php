<?php

if (strpos($device['sysDescr'], 'olive')) {
    $hardware = 'Olive';
    $serial = '';
} else {
    $junose_hardware = snmp_get($device, 'sysObjectID.0', '-Ovqs', '+Juniper-Products-MIB', 'junose');
    $junose_version = snmp_get($device, 'juniSystemSwVersion.0', '-Ovqs', '+Juniper-System-MIB', 'junose');
    $junose_serial = '';

    $hardware = 'Juniper ' . rewrite_junose_hardware($junose_hardware);
}

[$version] = explode(' ', $junose_version);
[,$version] = explode('(', $version);
[$features] = explode(']', $junose_version);
[,$features] = explode('[', $features);
