<?php

// APC
if ($device['os'] == 'apc') {
    // PDU - Phase
    $oids = snmp_walk($device, 'rPDUStatusPhaseIndex', '-OsqnU', 'PowerNet-MIB');
    if (empty($oids)) {
        $oids = snmp_walk($device, 'rPDULoadPhaseConfigIndex', '-OsqnU', 'PowerNet-MIB');
    }

    if ($oids) {
        d_echo($oids."\n");

        $oids = trim($oids);
        if ($oids) {
            echo 'APC PowerNet-MIB Phase ';
        }

        $type      = 'apc';
        $precision = '10';
        foreach (explode("\n", $oids) as $data) {
            $data = trim($data);
            if ($data) {
                list($oid,$kind) = explode(' ', $data);
                $split_oid       = explode('.', $oid);
                $index           = $split_oid[(count($split_oid) - 1)];

                $current_oid = '1.3.6.1.4.1.318.1.1.12.2.3.1.1.2.'.$index;
                // rPDULoadStatusLoad
                $phase_oid = '1.3.6.1.4.1.318.1.1.12.2.3.1.1.4.'.$index;
                // rPDULoadStatusPhaseNumber
                $limit_oid = '1.3.6.1.4.1.318.1.1.12.2.2.1.1.4.'.$index;
                // rPDULoadPhaseConfigOverloadThreshold
                $lowlimit_oid = '1.3.6.1.4.1.318.1.1.12.2.2.1.1.2.'.$index;
                // rPDULoadPhaseConfigLowLoadThreshold
                $warnlimit_oid = '1.3.6.1.4.1.318.1.1.12.2.2.1.1.3.'.$index;
                // rPDULoadPhaseConfigNearOverloadThreshold
                $phase   = snmp_get($device, $phase_oid, '-Oqv', '');
                $current = (snmp_get($device, $current_oid, '-Oqv', '') / $precision);
                $limit   = snmp_get($device, $limit_oid, '-Oqv', '');
                // No / $precision here! Nice, APC!
                $lowlimit = snmp_get($device, $lowlimit_oid, '-Oqv', '');
                // No / $precision here! Nice, APC!
                $warnlimit = snmp_get($device, $warnlimit_oid, '-Oqv', '');
                // No / $precision here! Nice, APC!
                if (count(explode("\n", $oids)) != 1) {
                    $descr = "Phase $phase";
                }
                else {
                    $descr = 'Output';
                }

                discover_sensor($valid['sensor'], 'current', $device, $current_oid, $index, $type, $descr, '10', '1', $lowlimit, null, $warnlimit, $limit, $current);
            }
        }
    }

    unset($oids);

    // v2 firmware- first bank is total, v3 firmware, 3rd bank is total
    $oids = snmp_walk($device, 'rPDULoadStatusIndex', '-OsqnU', 'PowerNet-MIB');
    // should work with firmware v2 and v3
    if ($oids) {
        echo 'APC PowerNet-MIB Banks ';
        d_echo($oids."\n");

        $oids      = trim($oids);
        $type      = 'apc';
        $precision = '10';

        // version 2 does some stuff differently- total power is first oid in index instead of the last.
        // will look something like "AOS v2.6.4 / App v2.6.5"
        $baseversion = '3';
        if (stristr($device['version'], 'AOS v2') == true) {
            $baseversion = '2';
        }

        foreach (explode("\n", $oids) as $data) {
            $data = trim($data);
            if ($data) {
                list($oid,$kind) = explode(' ', $data);
                $split_oid       = explode('.', $oid);

                $index = $split_oid[(count($split_oid) - 1)];

                $banknum = ($index - 1);
                $descr   = 'Bank '.$banknum;
                if ($baseversion == '3') {
                    if ($index == '1') {
                        $descr = 'Bank Total';
                    }
                }

                if ($baseversion == '2') {
                    if ($index == '1') {
                        $descr = 'Bank Total';
                    }
                }

                $current_oid = '1.3.6.1.4.1.318.1.1.12.2.3.1.1.2.'.$index;
                // rPDULoadStatusLoad
                $bank_oid = '1.3.6.1.4.1.318.1.1.12.2.3.1.1.5.'.$index;
                // rPDULoadStatusBankNumber
                $limit_oid = '1.3.6.1.4.1.318.1.1.12.2.4.1.1.4.'.$index;
                // rPDULoadBankConfigOverloadThreshold
                $lowlimit_oid = '1.3.6.1.4.1.318.1.1.12.2.4.1.1.2.'.$index;
                // rPDULoadBankConfigLowLoadThreshold
                $warnlimit_oid = '1.3.6.1.4.1.318.1.1.12.2.4.1.1.3.'.$index;
                // rPDULoadBankConfigNearOverloadThreshold
                $bank      = snmp_get($device, $bank_oid, '-Oqv', '');
                $current   = (snmp_get($device, $current_oid, '-Oqv', '') / $precision);
                $limit     = snmp_get($device, $limit_oid, '-Oqv', '');
                $lowlimit  = snmp_get($device, $lowlimit_oid, '-Oqv', '');
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
    $oids = snmp_walk($device, '1.3.6.1.4.1.318.1.1.26.9.4.3.1.1', '-t 30 -OsqnU', 'PowerNet-MIB');
    if ($oids) {
        echo 'APC PowerNet-MIB Outlets ';
        d_echo($oids."\n");

        $oids      = trim($oids);
        $type      = 'apc';
        $precision = '10';

        foreach (explode("\n", $oids) as $data) {
            $data = trim($data);
            if ($data) {
                list($oid,$kind) = explode(' ', $data);
                $split_oid       = explode('.', $oid);

                $index = $split_oid[(count($split_oid) - 1)];

                $voltage_oid = '1.3.6.1.4.1.318.1.1.26.6.3.1.6';
                // rPDU2PhaseStatusVoltage
                $current_oid = '1.3.6.1.4.1.318.1.1.26.9.4.3.1.6.'.$index;
                // rPDU2OutletMeteredStatusCurrent
                $limit_oid = '1.3.6.1.4.1.318.1.1.26.9.4.1.1.7.'.$index;
                // rPDU2OutletMeteredConfigOverloadCurrentThreshold
                $lowlimit_oid = '1.3.6.1.4.1.318.1.1.26.9.4.1.1.7.'.$index;
                // rPDU2OutletMeteredConfigLowLoadCurrentThreshold
                $warnlimit_oid = '1.3.6.1.4.1.318.1.1.26.9.4.1.1.6.'.$index;
                // rPDU2OutletMeteredConfigNearOverloadCurrentThreshold
                $name_oid = '1.3.6.1.4.1.318.1.1.26.9.4.3.1.3.'.$index;
                // rPDU2OutletMeteredStatusName
                $voltage = snmp_get($device, $voltage_oid, '-Oqv', '');

                $current   = (snmp_get($device, $current_oid, '-Oqv', '') / $precision);
                $limit     = (snmp_get($device, $limit_oid, '-Oqv', '') / $voltage);
                $lowlimit  = (snmp_get($device, $lowlimit_oid, '-Oqv', '') / $voltage);
                $warnlimit = (snmp_get($device, $warnlimit_oid, '-Oqv', '') / $voltage);
                $descr     = 'Outlet '.$index.' - '.snmp_get($device, $name_oid, '-Oqv', '');

                discover_sensor($valid['sensor'], 'current', $device, $current_oid, $index, $type, $descr, '10', '1', $lowlimit, null, $warnlimit, $limit, $current);
            }
        }
    }

    unset($oids);

    // ATS
    $oids = snmp_walk($device, 'atsConfigPhaseTableIndex', '-OsqnU', 'PowerNet-MIB');
    if ($oids) {
        $type = 'apc';
        d_echo($oids."\n");

        $oids = trim($oids);
        if ($oids) {
            echo 'APC PowerNet-MIB ATS ';
        }

        $current_oid = '1.3.6.1.4.1.318.1.1.8.5.4.3.1.4.1.1.1';
        // atsOutputCurrent
        $limit_oid = '1.3.6.1.4.1.318.1.1.8.4.16.1.5.1';
        // atsConfigPhaseOverLoadThreshold
        $lowlimit_oid = '1.3.6.1.4.1.318.1.1.8.4.16.1.3.1';
        // atsConfigPhaseLowLoadThreshold
        $warnlimit_oid = '1.3.6.1.4.1.318.1.1.8.4.16.1.4.1';
        // atsConfigPhaseNearOverLoadThreshold
        $index = 1;

        $current = (snmp_get($device, $current_oid, '-Oqv', '') / $precision);
        $limit   = snmp_get($device, $limit_oid, '-Oqv', '');
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
    $oid_array = array(
        array(
            'HighPrecOid' => 'upsHighPrecOutputCurrent',
            'AdvOid'      => 'upsAdvOutputCurrent',
            'type'        => 'apc',
            'index'       => 0,
            'descr'       => 'Current Drawn',
            'divisor'     => 10,
            'mib'         => '+PowerNet-MIB',
        ),
    );
    foreach ($oid_array as $item) {
        $low_limit      = null;
        $low_limit_warn = null;
        $warn_limit     = null;
        $high_limit     = null;
        $oids           = snmp_get($device, $item['HighPrecOid'].'.'.$item['index'], '-OsqnU', $item['mib']);
        if (empty($oids)) {
            $oids        = snmp_get($device, $item['AdvOid'].'.'.$item['index'], '-OsqnU', $item['mib']);
            $current_oid = $item['AdvOid'];
        }
        else {
            $current_oid = $item['HighPrecOid'];
        }

        if (!empty($oids)) {
            d_echo($oids."\n");

            $oids = trim($oids);
            if ($oids) {
                echo $item['type'].' '.$item['mib'].' UPS';
            }

            if (stristr($current_oid, 'HighPrec')) {
                $current = ($oids / $item['divisor']);
            }
            else {
                $current         = $oids;
                $item['divisor'] = 1;
            }

            discover_sensor($valid['sensor'], 'current', $device, $current_oid.'.'.$item['index'], $current_oid.'.'.$item['index'], $item['type'], $item['descr'], $item['divisor'], 1, $low_limit, $low_limit_warn, $warn_limit, $high_limit, $current);
        }
    }
}
