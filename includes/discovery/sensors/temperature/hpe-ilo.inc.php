<?php

$oids = snmp_walk($device, 'cpqHeTemperatureEntry', '-Osqn', 'CPQHLTH-MIB');
$oids2 = snmp_walk($device, 'cpqDaPhyDrvEntry', '-Osqn', 'CPQIDA-MIB');
d_echo($oids."\n");

$oids = trim($oids);
if ($oids) {
    echo 'HP ILO4 ';
}

//System
foreach (explode("\n", $oids) as $data) {
    $data = trim($data);
    if ($data) {
        list($oid,$status) = explode(' ', $data, 2);
        d_echo('status : '.$status."\n");

        if ($status == 'ok') {
            $split_oid          = explode('.', $oid);
            $index              = $split_oid[(count($split_oid) - 2)].'.'.$split_oid[(count($split_oid) - 1)];
            $descr_oid          = ".1.3.6.1.4.1.232.6.2.6.8.1.3.$index";
            $value_oid          = ".1.3.6.1.4.1.232.6.2.6.8.1.4.$index";
            $limit_oid          = ".1.3.6.1.4.1.232.6.2.6.8.1.5.$index";
            $warnlimit_oid      = ".1.3.6.1.4.1.232.6.2.6.8.1.5.$index";
            $lowwarnlimit_oid   = "";
            $lowlimit_oid       = "";

            $descr              = trim(snmp_get($device, $descr_oid, '-Oqv', 'CPQHLTH-MIB'), '"');
            $value              = snmp_get($device, $value_oid, '-Oqv', 'CPQHLTH-MIB');
            $warnlimit          = snmp_get($device, $warnlimit_oid, '-Oqv', 'CPQHLTH-MIB');
            $limit              = snmp_get($device, $limit_oid, '-Oqv', 'CPQHLTH-MIB');
            $lowwarnlimit       = 0;
            $lowlimit           = 0;

            if ($warnlimit == 0) {
                $warnlimit      = 90;
                $limit          = 90;
            }
            discover_sensor($valid['sensor'], 'temperature', $device, $value_oid, $index, 'hpe-ilo', $descr, '1', '1', $lowlimit, $low_warn_limit, $warnlimit, $limit, $value);
        }
    }//end if
}

//HDD
foreach (explode("\n", $oids2) as $data) {
    $data = trim($data);
    if ($data) {
        list($oid,$status) = explode(' ', $data, 2);
        d_echo('status : '.$status."\n");

        if ($status == 'ok') {
            $split_oid          = explode('.', $oid);
            $index              = $split_oid[(count($split_oid) - 2)].'.'.$split_oid[(count($split_oid) - 1)];
            $descr_oid          = ".1.3.6.1.4.1.232.3.2.5.1.1.64.$index";
            $value_oid          = ".1.3.6.1.4.1.232.3.2.5.1.1.70.$index";
            $limit_oid          = ".1.3.6.1.4.1.232.3.2.5.1.1.72.$index";
            $warnlimit_oid      = ".1.3.6.1.4.1.232.3.2.5.1.1.72.$index";
            $lowwarnlimit_oid   = "";
            $lowlimit_oid       = "";

            $descr              = trim(snmp_get($device, $descr_oid, '-Oqv', 'CPQIDA-MIB'), '"');
            $value              = snmp_get($device, $value_oid, '-Oqv', 'CPQIDA-MIB');
            $warnlimit          = snmp_get($device, $warnlimit_oid, '-Oqv', 'CPQIDA-MIB');
            $limit              = snmp_get($device, $limit_oid, '-Oqv', 'CPQIDA-MIB');
            $lowwarnlimit       = 0;
            $lowlimit           = 0;

            if ($warnlimit == 0) {
                $warnlimit      = 90;
                $limit          = 90;
            }
            discover_sensor($valid['sensor'], 'temperature', $device, $value_oid, $index, 'hpe-ilo', $descr, '1', '1', $lowlimit, $low_warn_limit, $warnlimit, $limit, $value);
        }
    }//end if
}
