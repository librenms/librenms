<?php

if (!$os) {
    $skip_oids = array(
        '.1.3.6.1.4.1.674.10892.2',
        '.1.3.6.1.4.1.17163.1.1',
        '.1.3.6.1.4.1.17713.21',
        '.1.3.6.1.4.1.2.3.51.3'
    );
    if (preg_match('/^Linux/', $sysDescr) && !in_array($sysObjectId, $skip_oids)) {
        $os = 'linux';
    }

    // Specific Linux-derivatives
    if ($os == 'linux') {
        // Check for QNAP Systems TurboNAS
        $entPhysicalMfgName = snmp_get($device, 'ENTITY-MIB::entPhysicalMfgName.1', '-Osqnv');

        if (strstr($sysObjectId, '.1.3.6.1.4.1.5528.100.20.10.2014') || strstr($sysObjectId, '.1.3.6.1.4.1.5528.100.20.10.2016')) {
            $os = 'netbotz';
        } elseif (strstr($sysDescr, 'endian')) {
            $os = 'endian';
        } elseif (preg_match('/Cisco Small Business/', $sysDescr)) {
            $os = 'ciscosmblinux';
        } elseif (strpos($entPhysicalMfgName, 'QNAP') !== false) {
            $os = 'qnap';
        } elseif (stristr($sysObjectId, 'packetlogic') || strstr($sysObjectId, '.1.3.6.1.4.1.15397.2')) {
            $os = 'procera';
        } elseif (strstr($sysObjectId, '.1.3.6.1.4.1.10002.1') || strstr($sysObjectId, '.1.3.6.1.4.1.41112.1.4') || strpos(trim(snmp_get($device, 'dot11manufacturerName.5', '-Osqnv', 'IEEE802dot11-MIB')), 'Ubiquiti') !== false) {
            $os = 'airos';
            if (strpos(trim(snmp_get($device, 'dot11manufacturerProductName.5', '-Osqnv', 'IEEE802dot11-MIB')), 'UAP') !== false) {
                $os = 'unifi';
            } elseif (strpos(trim(snmp_get($device, 'dot11manufacturerProductName.2', '-Osqnv', 'IEEE802dot11-MIB')), 'UAP') !== false) {
                $os = 'unifi';
            } elseif (strpos(trim(snmp_get($device, 'dot11manufacturerProductName.3', '-Osqnv', 'IEEE802dot11-MIB')), 'UAP') !== false) {
                $os = 'unifi';
            } elseif (strpos(trim(snmp_get($device, 'dot11manufacturerProductName.4', '-Osqnv', 'IEEE802dot11-MIB')), 'UAP') !== false) {
                $os = 'unifi';
            } elseif (strpos(trim(snmp_get($device, 'dot11manufacturerProductName.6', '-Osqnv', 'IEEE802dot11-MIB')), 'UAP') !== false) {
                $os = 'unifi';
            } elseif (trim(snmp_get($device, 'fwVersion.1', '-Osqnv', 'UBNT-AirFIBER-MIB')) != '') {
                $os = 'airos-af';
            }
        } elseif (snmp_get($device, 'GANDI-MIB::rxCounter.0', '-Osqnv', 'GANDI-MIB') !== false) {
            $os = 'pktj';
            $pktj_mibs = array (
               "rxCounter"       => "GANDI-MIB",  // RX Packets
               "txCounter"       => "GANDI-MIB",  // TX Packets
               "dropCounter"     => "GANDI-MIB",  // Dropped counters
               "acldropCounter"  => "GANDI-MIB",  // ACL Dropped counter
               "ratedropCounter" => "GANDI-MIB",  // Rate Dropped counter
               "KNIrxCounter"    => "GANDI-MIB",  // KNI RX counter
               "KNItxCounter"    => "GANDI-MIB",  // KNI TX counter
               "KNIdropCounter"  => "GANDI-MIB",  // KNI DROP counter
            );
            register_mibs($device, $pktj_mibs, "include/discovery/os/linux.inc.php");
        } elseif (stristr($sysObjectId, 'cumulusMib') || strstr($sysObjectId, '.1.3.6.1.4.1.40310')) {
            $os = 'cumulus';
        } elseif (strstr($sysDescr, 'g56fa85e') || strstr($sysDescr, 'gc80f187') || strstr($sysDescr, 'g829be90') || strstr($sysDescr, 'g63c0044')) {
            $os = 'sophos';
        } elseif (snmp_get($device, 'SFA-INFO::systemName.0', '-Osqnv', 'SFA-INFO') !== false) {
            $os = 'ddnos';
        } else {
            // Check for Synology DSM
            $hrSystemInitialLoadParameters = trim(snmp_get($device, 'HOST-RESOURCES-MIB::hrSystemInitialLoadParameters.0', '-Osqnv'));

            if (strpos($hrSystemInitialLoadParameters, 'syno_hw_version') !== false) {
                $os = 'dsm';
            } else {
                // Check for Carel PCOweb
                $roomTemp = trim(snmp_get($device, 'roomTemp.0', '-OqvU', 'CAREL-ug40cdz-MIB'));

                if (is_numeric($roomTemp)) {
                    $os = 'pcoweb';
                }
            }
        }
    }
}
