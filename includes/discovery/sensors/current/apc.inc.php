<?php

// PDU - Phase
$oids = snmp_walk($device, 'rPDUStatusPhaseIndex', '-OsqnU', 'PowerNet-MIB');
if (isset($oids) && $oids) {
    d_echo($oids . "\n");
    $oids = trim($oids);
    if ($oids) {
        echo 'APC PowerNet-MIB Phase ';
    }
    $type = 'apc';
    $precision = '10';
    foreach (explode("\n", $oids) as $data) {
        $data = trim($data);
        if ($data) {
            [$oid,$kind] = explode(' ', $data);
            $split_oid = explode('.', $oid);
            $index = $split_oid[count($split_oid) - 1];
            $current_oid = '.1.3.6.1.4.1.318.1.1.12.2.3.1.1.2.' . $index;
            // rPDULoadStatusLoad
            $phase_oid = '.1.3.6.1.4.1.318.1.1.12.2.3.1.1.4.' . $index;
            // rPDULoadStatusPhaseNumber
            $limit_oid = '.1.3.6.1.4.1.318.1.1.12.2.2.1.1.4.' . $index;
            // rPDULoadPhaseConfigOverloadThreshold
            $lowlimit_oid = '.1.3.6.1.4.1.318.1.1.12.2.2.1.1.2.' . $index;
            // rPDULoadPhaseConfigLowLoadThreshold
            $warnlimit_oid = '.1.3.6.1.4.1.318.1.1.12.2.2.1.1.3.' . $index;
            // rPDULoadPhaseConfigNearOverloadThreshold
            $phase = snmp_get($device, $phase_oid, '-Oqv', '');
            $current = (snmp_get($device, $current_oid, '-Oqv', '') / $precision);
            $limit = snmp_get($device, $limit_oid, '-Oqv', '');
            // No / $precision here! Nice, APC!
            $lowlimit = snmp_get($device, $lowlimit_oid, '-Oqv', '');
            // No / $precision here! Nice, APC!
            $warnlimit = snmp_get($device, $warnlimit_oid, '-Oqv', '');
            // No / $precision here! Nice, APC!
            if (count(explode("\n", $oids)) != 1) {
                $descr = "Phase $phase";
            } else {
                $descr = 'Output';
            }
            discover_sensor($valid['sensor'], 'current', $device, $current_oid, $index, $type, $descr, '10', '1', $lowlimit, null, $warnlimit, $limit, $current);
        }
    }
}
unset($oids);
$oids = snmp_walk($device, 'rPDULoadStatusPhaseNumber', '-OsqnU', 'PowerNet-MIB');
if (isset($oids) && $oids) {
    d_echo($oids . "\n");
    $oids = trim($oids);
    if ($oids) {
        echo 'APC PowerNet-MIB Phase ';
    }
    $type = 'apc';
    $precision = '10';
    foreach (explode("\n", $oids) as $data) {
        $data = trim($data);
        if ($data) {
            [$oid,$phasenum] = explode(' ', $data);
            // If the phasenum is 0, we can ignore becuase it's bank data
            if ($phasenum == 0) {
                continue;
            }
            $split_oid = explode('.', $oid);
            $index = $split_oid[count($split_oid) - 1];
            $current_oid = '.1.3.6.1.4.1.318.1.1.12.2.3.1.1.2.' . $index;
            // rPDULoadStatusLoad
            $phase_oid = '.1.3.6.1.4.1.318.1.1.12.2.3.1.1.4.' . $index;
            // rPDULoadStatusPhaseNumber
            $limit_oid = '.1.3.6.1.4.1.318.1.1.12.2.2.1.1.4.' . $phasenum;
            // rPDULoadPhaseConfigOverloadThreshold
            $lowlimit_oid = '.1.3.6.1.4.1.318.1.1.12.2.2.1.1.2.' . $phasenum;
            // rPDULoadPhaseConfigLowLoadThreshold
            $warnlimit_oid = '.1.3.6.1.4.1.318.1.1.12.2.2.1.1.3.' . $phasenum;
            // rPDULoadPhaseConfigNearOverloadThreshold
            $phase = snmp_get($device, $phase_oid, '-Oqv', '');
            $current = (snmp_get($device, $current_oid, '-Oqv', '') / $precision);
            $limit = snmp_get($device, $limit_oid, '-Oqv', '');
            // No / $precision here! Nice, APC!
            $lowlimit = snmp_get($device, $lowlimit_oid, '-Oqv', '');
            // No / $precision here! Nice, APC!
            $warnlimit = snmp_get($device, $warnlimit_oid, '-Oqv', '');
            // No / $precision here! Nice, APC!
            if (count(explode("\n", $oids)) != 1) {
                $descr = "Phase $phase";
            } else {
                $descr = 'Output';
            }
            discover_sensor($valid['sensor'], 'current', $device, $current_oid, $index, $type, $descr, '10', '1', $lowlimit, null, $warnlimit, $limit, $current);
        }
    }
}
unset($oids);
// v2 firmware- first bank is total, v3 firmware, 3rd bank is total
$bank_count = snmp_get($device, 'rPDULoadDevNumBanks.0', '-Oqv', 'PowerNet-MIB');
if ($bank_count > 0) {
    $oids = snmp_walk($device, 'rPDULoadStatusBankNumber', '-OsqnU', 'PowerNet-MIB');
}
// should work with firmware v2 and v3
if (isset($oids) && $oids) {
    echo 'APC PowerNet-MIB Banks ';
    d_echo($oids . "\n");
    $oids = trim($oids);
    $type = 'apc';
    $precision = '10';
    foreach (explode("\n", $oids) as $data) {
        $data = trim($data);
        if ($data) {
            [$oid,$banknum] = explode(' ', $data);
            // If the banknum is 0, we can ignore becuase it's phase data
            if ($banknum == 0) {
                continue;
            }
            $split_oid = explode('.', $oid);
            $index = $split_oid[count($split_oid) - 1];
            $descr = 'Bank ' . $banknum;
            $current_oid = '.1.3.6.1.4.1.318.1.1.12.2.3.1.1.2.' . $index;
            // rPDULoadStatusLoad
            $bank_oid = '.1.3.6.1.4.1.318.1.1.12.2.3.1.1.5.' . $index;
            // rPDULoadStatusBankNumber
            $limit_oid = '.1.3.6.1.4.1.318.1.1.12.2.4.1.1.4.' . $banknum;
            // rPDULoadBankConfigOverloadThreshold
            $lowlimit_oid = '.1.3.6.1.4.1.318.1.1.12.2.4.1.1.2.' . $banknum;
            // rPDULoadBankConfigLowLoadThreshold
            $warnlimit_oid = '.1.3.6.1.4.1.318.1.1.12.2.4.1.1.3.' . $banknum;
            // rPDULoadBankConfigNearOverloadThreshold
            $bank = snmp_get($device, $bank_oid, '-Oqv', '');
            $current = (snmp_get($device, $current_oid, '-Oqv', '') / $precision);
            $limit = snmp_get($device, $limit_oid, '-Oqv', '');
            $lowlimit = snmp_get($device, $lowlimit_oid, '-Oqv', '');
            $warnlimit = snmp_get($device, $warnlimit_oid, '-Oqv', '');
            if ($limit != -1 && $lowlimit != -1 && $warnlimit != -1) {
                discover_sensor($valid['sensor'], 'current', $device, $current_oid, $index, $type, $descr, '10', '1', $lowlimit, null, $warnlimit, $limit, $current);
            }
        }
    }
    unset($baseversion);
}
unset($oids);
// Per Outlet Power Bar
$oids = snmp_walk($device, '.1.3.6.1.4.1.318.1.1.26.9.4.3.1.1', '-t 30 -OsqnU', 'PowerNet-MIB');
if (isset($oids) && $oids) {
    echo 'APC PowerNet-MIB Outlets ';
    d_echo($oids . "\n");
    $oids = trim($oids);
    $type = 'apc';
    $precision = '10';
    foreach (explode("\n", $oids) as $data) {
        $data = trim($data);
        if ($data) {
            [$oid,$kind] = explode(' ', $data);
            $split_oid = explode('.', $oid);
            $index = $split_oid[count($split_oid) - 1];
            $voltage_oid = '.1.3.6.1.4.1.318.1.1.26.6.3.1.6';
            // rPDU2PhaseStatusVoltage
            $current_oid = '.1.3.6.1.4.1.318.1.1.26.9.4.3.1.6.' . $index;
            // rPDU2OutletMeteredStatusCurrent
            $limit_oid = '.1.3.6.1.4.1.318.1.1.26.9.4.1.1.7.' . $index;
            // rPDU2OutletMeteredConfigOverloadCurrentThreshold
            $lowlimit_oid = '.1.3.6.1.4.1.318.1.1.26.9.4.1.1.7.' . $index;
            // rPDU2OutletMeteredConfigLowLoadCurrentThreshold
            $warnlimit_oid = '.1.3.6.1.4.1.318.1.1.26.9.4.1.1.6.' . $index;
            // rPDU2OutletMeteredConfigNearOverloadCurrentThreshold
            $name_oid = '.1.3.6.1.4.1.318.1.1.26.9.4.3.1.3.' . $index;
            // rPDU2OutletMeteredStatusName
            $voltage = snmp_get($device, $voltage_oid, '-Oqv', '');
            $current = (snmp_get($device, $current_oid, '-Oqv', '') / $precision);

            $limit = null;
            $lowlimit = null;
            $warnlimit = null;

            if ($voltage) {
                $limit = (snmp_get($device, $limit_oid, '-Oqv', '') / $voltage);
                $lowlimit = (snmp_get($device, $lowlimit_oid, '-Oqv', '') / $voltage);
                $warnlimit = (snmp_get($device, $warnlimit_oid, '-Oqv', '') / $voltage);
            }

            $descr = 'Outlet ' . $index . ' - ' . snmp_get($device, $name_oid, '-Oqv', '');
            discover_sensor($valid['sensor'], 'current', $device, $current_oid, $index, $type, $descr, '10', '1', $lowlimit, null, $warnlimit, $limit, $current);
        }
    }
}
unset($oids);
// ATS
$oids = snmp_walk($device, 'atsConfigPhaseTableIndex', '-OsqnU', 'PowerNet-MIB');
if (isset($oids) && $oids) {
    $type = 'apc';
    $precision = '10';
    d_echo($oids . "\n");
    $oids = trim($oids);
    if ($oids) {
        echo 'APC PowerNet-MIB ATS ';
    }
    $current_oid = '.1.3.6.1.4.1.318.1.1.8.5.4.3.1.4.1.1.1';
    // atsOutputCurrent
    $limit_oid = '.1.3.6.1.4.1.318.1.1.8.4.16.1.5.1';
    // atsConfigPhaseOverLoadThreshold
    $lowlimit_oid = '.1.3.6.1.4.1.318.1.1.8.4.16.1.3.1';
    // atsConfigPhaseLowLoadThreshold
    $warnlimit_oid = '.1.3.6.1.4.1.318.1.1.8.4.16.1.4.1';
    // atsConfigPhaseNearOverLoadThreshold
    $index = 1;
    $current = (snmp_get($device, $current_oid, '-Oqv', '') / $precision);
    $limit = snmp_get($device, $limit_oid, '-Oqv', '');
    // No / $precision here! Nice, APC!
    $lowlimit = snmp_get($device, $lowlimit_oid, '-Oqv', '');
    // No / $precision here! Nice, APC!
    $warnlimit = snmp_get($device, $warnlimit_oid, '-Oqv', '');
    // No / $precision here! Nice, APC!
    $descr = 'Output Feed';
    discover_sensor($valid['sensor'], 'current', $device, $current_oid, $index, $type, $descr, '10', '1', $lowlimit, null, $warnlimit, $limit, $current);
}
unset($oids);

// UPS

$phasecount = $pre_cache['apcups_phase_count'];
if ($phasecount > 2) {
    $oids = snmpwalk_cache_oid($device, 'upsPhaseOutputCurrent', [], 'PowerNet-MIB');
    $in_oids = snmpwalk_cache_oid($device, 'upsPhaseInputCurrent', $in_oids, 'PowerNet-MIB');
} else {
    $oids = snmpwalk_cache_oid($device, 'upsHighPrecOutputCurrent', [], 'PowerNet-MIB');
}
if (isset($in_oids)) {
    foreach ($in_oids as $index => $data) {
        $type = 'apcUPS';
        $current_oid = '.1.3.6.1.4.1.318.1.1.1.9.2.3.1.6.' . $index;
        $divisor = 10;
        $current = $data['upsPhaseInputCurrent'] / $divisor;
        $in_index = '3.1.4.' . $index;
        if (substr($index, 0, 1) == 2 && $data['upsPhaseInputCurrent'] != -1) {
            $descr = 'Phase ' . substr($index, -1) . ' Bypass Input';
            discover_sensor($valid['sensor'], 'current', $device, $current_oid, $in_index, $type, $descr, $divisor, 0, null, null, null, null, $current);
        } elseif (substr($index, 0, 1) == 1) {
            $descr = 'Phase ' . substr($index, -1) . ' Input';
            discover_sensor($valid['sensor'], 'current', $device, $current_oid, $in_index, $type, $descr, $divisor, 0, null, null, null, null, $current);
        }
    }
}
unset($index);
unset($data);
foreach ($oids as $index => $data) {
    $type = 'apcUPS';
    $descr = 'Phase ' . substr($index, -1) . ' Output';
    if (isset($data['upsHighPrecOutputCurrent'])) {
        $current_oid = '.1.3.6.1.4.1.318.1.1.1.4.3.4.' . $index;
        $divisor = 10;
        $current = $data['upsHighPrecOutputCurrent'] / $divisor;
    } else {
        $current_oid = '.1.3.6.1.4.1.318.1.1.1.9.3.3.1.4.' . $index;
        $divisor = 10;
        $current = $data['upsPhaseOutputCurrent'] / $divisor;
    }
    if ($current >= -1) {
        discover_sensor($valid['sensor'], 'current', $device, $current_oid, $index, $type, $descr, $divisor, 1, null, null, null, null, $current);
    }
}
unset($index);
unset($data);
