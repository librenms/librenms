<?php

if (!$os) {
    $skip_oids = array(
        '.1.3.6.1.4.1.674.10892.2',
        '.1.3.6.1.4.1.17163.1.1',
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
        }
        else if (strstr($sysDescr, 'endian')) {
            $os = 'endian';
        }
        else if (preg_match('/Cisco Small Business/', $sysDescr)) {
            $os = 'ciscosmblinux';
        }
        else if (strpos($entPhysicalMfgName, 'QNAP') !== false) {
            $os = 'qnap';
        }
        else if (stristr($sysObjectId, 'packetlogic') || strstr($sysObjectId, '.1.3.6.1.4.1.15397.2')) {
            $os = 'procera';
        }
        else if (strstr($sysObjectId, '.1.3.6.1.4.1.10002.1') || strstr($sysObjectId, '.1.3.6.1.4.1.41112.1.4') || strpos(trim(snmp_get($device, 'dot11manufacturerName.5', '-Osqnv', 'IEEE802dot11-MIB')), 'Ubiquiti') !== false) {
            $os = 'airos';
            if (strpos(trim(snmp_get($device, 'dot11manufacturerProductName.5', '-Osqnv', 'IEEE802dot11-MIB')), 'UAP') !== false) {
                $os = 'unifi';
            }
            else if (strpos(trim(snmp_get($device, 'dot11manufacturerProductName.2', '-Osqnv', 'IEEE802dot11-MIB')), 'UAP') !== false) {
                $os = 'unifi';
            }
            else if (strpos(trim(snmp_get($device, 'dot11manufacturerProductName.3', '-Osqnv', 'IEEE802dot11-MIB')), 'UAP') !== false) {
                $os = 'unifi';
            }
            else if (strpos(trim(snmp_get($device, 'dot11manufacturerProductName.4', '-Osqnv', 'IEEE802dot11-MIB')), 'UAP') !== false) {
                $os = 'unifi';
            }
            else if (strpos(trim(snmp_get($device, 'dot11manufacturerProductName.6', '-Osqnv', 'IEEE802dot11-MIB')), 'UAP') !== false) {
                $os = 'unifi';
            }
            else if (trim(snmp_get($device, 'fwVersion.1', '-Osqnv', 'UBNT-AirFIBER-MIB')) != '') {
                $os = 'airos-af';
            }
        }
        else {
            // Check for Synology DSM
            $hrSystemInitialLoadParameters = trim(snmp_get($device, 'HOST-RESOURCES-MIB::hrSystemInitialLoadParameters.0', '-Osqnv'));

            if (strpos($hrSystemInitialLoadParameters, 'syno_hw_version') !== false) {
                $os = 'dsm';
            }
            else {
                // Check for Carel PCOweb
                $roomTemp = trim(snmp_get($device, 'roomTemp.0', '-OqvU', 'CAREL-ug40cdz-MIB'));

                if (is_numeric($roomTemp)) {
                    $os = 'pcoweb';
                }
            }
        }
    }
}
