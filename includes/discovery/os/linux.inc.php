<?php


$skip_oids = array(
    '.1.3.6.1.4.1.674.10892.2',
    '.1.3.6.1.4.1.17163.1.1',
    '.1.3.6.1.4.1.17713.21',
    '.1.3.6.1.4.1.2.3.51.3',
    '.1.3.6.1.4.1.7779.', // nios
    '.1.3.6.1.4.1.9.1.1348' // Cisco Unified Communications Manager
);

if (starts_with($sysDescr, 'Linux') && !starts_with($sysObjectId, $skip_oids)) {
    $os = 'linux';

    // Specific Linux-derivatives
    if (starts_with($sysObjectId, array('.1.3.6.1.4.1.5528.100.20.10.2014', '.1.3.6.1.4.1.5528.100.20.10.2016'))) {
        $os = 'netbotz';
    } elseif (str_contains($sysDescr, 'endian')) {
        $os = 'endian';
    } elseif (str_contains($sysDescr, 'Cisco Small Business')) {
        $os = 'ciscosmblinux';
    } elseif (str_contains(snmp_get($device, 'ENTITY-MIB::entPhysicalMfgName.1', '-Osqnv'), 'QNAP')) {
        $os = 'qnap';
    } elseif (starts_with($sysObjectId, '.1.3.6.1.4.1.15397.2')) {
        $os = 'procera';
    } elseif (starts_with($sysObjectId, array('.1.3.6.1.4.1.10002.1', '.1.3.6.1.4.1.41112.1.4')) || str_contains(snmp_get($device, 'dot11manufacturerName.5', '-Osqnv', 'IEEE802dot11-MIB'), 'Ubiquiti')) {
        $os = 'airos';
        if (str_contains(snmp_walk($device, 'dot11manufacturerProductName', '-Osqnv', 'IEEE802dot11-MIB'), 'UAP')) {
            $os = 'unifi';
        } elseif (snmp_get($device, 'fwVersion.1', '-Osqnv', 'UBNT-AirFIBER-MIB') !== false) {
            $os = 'airos-af';
        }
    } elseif (snmp_get($device, 'GANDI-MIB::rxCounter.0', '-Osqnv', 'GANDI-MIB') !== false) {
        $os = 'pktj';
        $pktj_mibs = array(
            "rxCounter" => "GANDI-MIB",  // RX Packets
            "txCounter" => "GANDI-MIB",  // TX Packets
            "dropCounter" => "GANDI-MIB",  // Dropped counters
            "acldropCounter" => "GANDI-MIB",  // ACL Dropped counter
            "ratedropCounter" => "GANDI-MIB",  // Rate Dropped counter
            "KNIrxCounter" => "GANDI-MIB",  // KNI RX counter
            "KNItxCounter" => "GANDI-MIB",  // KNI TX counter
            "KNIdropCounter" => "GANDI-MIB",  // KNI DROP counter
        );
        register_mibs($device, $pktj_mibs, "include/discovery/os/linux.inc.php");
    } elseif (starts_with($sysObjectId, '.1.3.6.1.4.1.40310')) {
        $os = 'cumulus';
    } elseif (str_contains($sysDescr, array('g56fa85e', 'gc80f187', 'g829be90', 'g63c0044', 'gba768e5'))) {
        $os = 'sophos';
    } elseif (snmp_get($device, 'SFA-INFO::systemName.0', '-Osqnv', 'SFA-INFO') !== false) {
        $os = 'ddnos';
    } elseif (str_contains(snmp_get($device, 'HOST-RESOURCES-MIB::hrSystemInitialLoadParameters.0', '-Osqnv'), 'syno_hw_version')) {
        $os = 'dsm'; // Synology DSM
    } elseif (is_numeric(trim(snmp_get($device, 'roomTemp.0', '-OqvU', 'CAREL-ug40cdz-MIB')))) {
        $os = 'pcoweb'; // Carel PCOweb
    }
}
