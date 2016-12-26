<?php

// APC
if ($device['os'] == 'apc') {
    echo 'APC ';
    // Phases
    $oids = snmpwalk_cache_oid($device, 'rPDULoadStatusPhaseNumber', array(), 'PowerNet-MIB');

    if (!empty($oids)) {
        echo 'Phase ';
        $oids = snmpwalk_cache_oid($device, 'rPDULoadStatusLoad', $oids, 'PowerNet-MIB');
        $oids = snmpwalk_cache_oid($device, 'rPDULoadPhaseConfigOverloadThreshold', $oids, 'PowerNet-MIB', null, '-OQUsb');
        $oids = snmpwalk_cache_oid($device, 'rPDULoadPhaseConfigLowLoadThreshold', $oids, 'PowerNet-MIB', null, '-OQUsb');
        $oids = snmpwalk_cache_oid($device, 'rPDULoadPhaseConfigNearOverloadThreshold', $oids, 'PowerNet-MIB', null, '-OQUsb');
    }

    foreach ($oids as $index => $data) {
        $type = 'apcPhase';
        $divisor = 10;
        if (count($oids) > 1) {
            $descr = 'Phase ' . $data['rPDULoadStatusPhaseNumber'];
        } else {
            $descr = 'Output';
        }

        $current_oid = '.1.3.6.1.4.1.318.1.1.12.2.3.1.1.2.' . $index;
        $current = $data['rPDULoadStatusLoad'] / $divisor;
        $limit = $data['rPDULoadPhaseConfigOverloadThreshold'];
        $lowlimit = $data['rPDULoadPhaseConfigLowLoadThreshold'];
        $warnlimit = $data['rPDULoadPhaseConfigNearOverloadThreshold'];

        discover_sensor($valid['sensor'], 'current', $device, $current_oid, $index, $type, $descr, $divisor, 1, $lowlimit, null, $warnlimit, $limit, $current);
    }

    // Banks
    $oids = array();
    $bank_count = snmp_get($device, 'rPDULoadDevNumBanks.0', '-Oqv', 'PowerNet-MIB');
    if ($bank_count > 0) {
        echo 'Banks ';
        $oids = snmpwalk_cache_oid($device, 'rPDULoadStatusLoad', $oids, 'PowerNet-MIB');
        $oids = snmpwalk_cache_oid($device, 'rPDULoadStatusBankNumber', $oids, 'PowerNet-MIB');
        $oids = snmpwalk_cache_oid($device, 'rPDULoadBankConfigOverloadThreshold', $oids, 'PowerNet-MIB');
        $oids = snmpwalk_cache_oid($device, 'rPDULoadBankConfigLowLoadThreshold', $oids, 'PowerNet-MIB');
        $oids = snmpwalk_cache_oid($device, 'rPDULoadBankConfigNearOverloadThreshold', $oids, 'PowerNet-MIB');
    }

        // version 2 does some stuff differently- total power is first oid in index instead of the last.
        // will look something like "AOS v2.6.4 / App v2.6.5"
    $baseversion = 3;
    if (str_contains($device['version'], 'AOS v2')) {
        $baseversion = 2;
    }

    foreach ($oids as $index => $data) {
        $type = 'apcBanks';
        $divisor = 10;

        if (($baseversion == 3 && $index == count($oids)) ||
            ($baseversion == 2 && $index == 1)
        ) {
                        $descr = 'Bank Total';
        } else {
            $descr = 'Bank ' . $data['rPDULoadStatusBankNumber'];
        }

        $current_oid = '.1.3.6.1.4.1.318.1.1.12.2.3.1.1.2.' . $index;
        $current = $data['rPDULoadStatusLoad'] / $divisor;
        $limit = $data['rPDULoadBankConfigOverloadThreshold'];
        $lowlimit = $data['rPDULoadBankConfigLowLoadThreshold'];
        $warnlimit = $data['rPDULoadBankConfigNearOverloadThreshold'];

        discover_sensor($valid['sensor'], 'current', $device, $current_oid, $index, $type, $descr, $divisor, 1, $lowlimit, null, $warnlimit, $limit, $current);
    }

    // Per Outlet Power Bar
    $oids = snmpwalk_cache_oid($device, 'rPDU2OutletMeteredStatusCurrent', array(), 'PowerNet-MIB');
    if (!empty($oids) && is_numeric(key($oids))) {
        echo 'Outlets ';
        $oids = snmpwalk_cache_oid($device, 'rPDU2PhaseStatusVoltage', $oids, 'PowerNet-MIB');
        $oids = snmpwalk_cache_oid($device, 'rPDU2OutletMeteredConfigOverloadCurrentThreshold', $oids, 'PowerNet-MIB');
        $oids = snmpwalk_cache_oid($device, 'rPDU2OutletMeteredConfigLowLoadCurrentThreshold', $oids, 'PowerNet-MIB');
        $oids = snmpwalk_cache_oid($device, 'rPDU2OutletMeteredConfigNearOverloadCurrentThreshold', $oids, 'PowerNet-MIB');
        $oids = snmpwalk_cache_oid($device, 'rPDU2OutletMeteredStatusName', $oids, 'PowerNet-MIB');
    }

    foreach ($oids as $index => $data) {
        $type = 'apcOutlet';
        $divisor = 10;
        $descr = 'Outlet ' . $index;
        if (isset($data['rPDU2OutletMeteredStatusName'])) {
            $descr .= ' - ' . $data['rPDU2OutletMeteredStatusName'];
        }
        $voltage = $data['rPDU2PhaseStatusVoltage'];
        $current = $data['rPDU2OutletMeteredStatusCurrent'] / $divisor;
        $current_oid = '.1.3.6.1.4.1.318.1.1.26.9.4.3.1.6.' . $index;
        $limit = $data['rPDU2OutletMeteredConfigOverloadCurrentThreshold'] / $voltage;
        $lowlimit = $data['rPDU2OutletMeteredConfigLowLoadCurrentThreshold'] / $voltage;
        $warnlimit = $data['rPDU2OutletMeteredConfigNearOverloadCurrentThreshold'] / $voltage;

        if (is_numeric($index)) {
            discover_sensor($valid['sensor'], 'current', $device, $current_oid, 'Outlets'.$index, $type, $descr, $divisor, 1, $lowlimit, null, $warnlimit, $limit, $current);
        }
    }

    // ATS
    $oids = snmpwalk_cache_oid($device, 'atsOutputCurrent', array(), 'PowerNet-MIB');
    if (!empty($outputFeedOids)) {
        echo 'ATS ';
        $oids = snmpwalk_cache_oid($device, 'atsConfigPhaseOverLoadThreshold', $oids, 'PowerNet-MIB');
        $oids = snmpwalk_cache_oid($device, 'atsConfigPhaseLowLoadThreshold', $oids, 'PowerNet-MIB');
        $oids = snmpwalk_cache_oid($device, 'atsConfigPhaseNearOverLoadThreshold', $oids, 'PowerNet-MIB');
    }

    foreach ($oids as $index => $data) {
        $type = 'apcATS';
        $descr = 'Output Feed';
        $divisor = 10;
        $current = $data['atsOutputCurrent'] / $divisor;
        $current_oid = '.1.3.6.1.4.1.318.1.1.8.5.4.3.1.4.1.1.' . $index;
        $limit = $data['atsConfigPhaseOverLoadThreshold'];
        $lowlimit = $data['atsConfigPhaseLowLoadThreshold'];
        $warnlimit = $data['atsConfigPhaseNearOverLoadThreshold'];

        discover_sensor($valid['sensor'], 'current', $device, $current_oid, $index, $type, $descr, $divisor, 1, $lowlimit, null, $warnlimit, $limit, $current);
    }

    // UPS
    $oids = snmpwalk_cache_oid($device, 'upsHighPrecOutputCurrent', array(), 'PowerNet-MIB');
    if (empty($oids)) {
        $oids = snmpwalk_cache_oid($device, 'upsAdvOutputCurrent', $oids, 'PowerNet-MIB');
    }

    foreach ($oids as $index => $data) {
        $type = 'apcUPS';
        $descr = 'Current Drawn';

        if (isset($data['upsHighPrecOutputCurrent'])) {
            $current_oid = '.1.3.6.1.4.1.318.1.1.1.4.3.4.' . $index;
            $divisor = 10;
            $current = $data['upsHighPrecOutputCurrent'] / $divisor;
        } else {
            $current_oid = '.1.3.6.1.4.F1.318.1.1.1.4.2.4.' . $index;
            $divisor = 1;
            $current = $data['upsAdvOutputCurrent'];
        }

        discover_sensor($valid['sensor'], 'current', $device, $current_oid, $index, $type, $descr, $divisor, 1, null, null, null, null, $current);
    }
}
