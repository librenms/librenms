<?php


if (starts_with($sysDescr, 'Linux') || starts_with($sysObjectId, '.1.3.6.1.4.1.8072.3.2.10')) {
    $os = 'linux';

    // Specific Linux-derivatives
    if (starts_with($sysObjectId, array('.1.3.6.1.4.1.10002.1', '.1.3.6.1.4.1.41112.1.4')) || str_contains(snmp_get($device, 'dot11manufacturerName.5', '-Osqnv', 'IEEE802dot11-MIB'), 'Ubiquiti')) {
        $os = 'airos';
        if (str_contains(snmp_walk($device, 'dot11manufacturerProductName', '-Osqnv', 'IEEE802dot11-MIB'), 'UAP')) {
            $os = 'unifi';
        } elseif (snmp_get($device, 'fwVersion.1', '-Osqnv', 'UBNT-AirFIBER-MIB', 'ubnt') !== false) {
            $os = 'airos-af';
        }
    } elseif (snmp_get($device, 'GANDI-MIB::rxCounter.0', '-Osqnv', 'GANDI-MIB') !== false) {
        $os = 'pktj';
    } elseif (snmp_get($device, 'SFA-INFO::systemName.0', '-Osqnv', 'SFA-INFO') !== false) {
        $os = 'ddnos';
    } elseif (is_numeric(trim(snmp_get($device, 'roomTemp.0', '-OqvU', 'CAREL-ug40cdz-MIB', 'carel')))) {
        $os = 'pcoweb'; // Carel PCOweb
    } elseif ($wrt = snmp_get($device, '.1.3.6.1.4.1.2021.7890.1.101.1', '-Osqnv')) {
        $wrt = trim($wrt, '"');
        if (starts_with($wrt, 'ASUSWRT-Merlin')) {
            $os = 'asuswrt-merlin';
        } elseif (starts_with($wrt, 'Tomato ')) {
            $os = 'tomato';
        }
    } elseif (preg_match('/^QNAP Systems/', snmp_get($device, "entPhysicalMfgName.1", "-Ovqn", "ENTITY-MIB"))) {
        $os = 'qnap';
    }
}
