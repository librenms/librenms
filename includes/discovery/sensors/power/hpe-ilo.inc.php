<?php

$oids = snmp_walk($device, 'cpqHeFltTolPowerSupplyEntry', '-Osqn', 'CPQHLTH-MIB');
d_echo($oids."\n");

$oids = trim($oids);
if ($oids) {
    echo 'HP ILO4 ';
}

foreach (explode("\n", $oids) as $data) {
    $data = trim($data);
    if ($data) {
        list($oid,$status) = explode(' ', $data, 2);
        d_echo('status : '.$status."\n");

        if ($status == 'ok') {
            $split_oid          = explode('.', $oid);
            $index              = $split_oid[(count($split_oid) - 2)].'.'.$split_oid[(count($split_oid) - 1)];
            $descr_oid          = ".1.3.6.1.4.1.232.6.2.9.3.1.2.$index";
            $value_oid          = ".1.3.6.1.4.1.232.6.2.9.3.1.7.$index";
            $limit_oid          = ".1.3.6.1.4.1.232.6.2.9.3.1.8.$index";
            $warnlimit_oid      = ".1.3.6.1.4.1.232.6.2.9.3.1.8.$index";
            $lowwarnlimit_oid   = "";
            $lowlimit_oid       = "";

            $descr              = 'PowerSupply #'.trim(snmp_get($device, $descr_oid, "-Oqv", "CPQHLTH-MIB"), '"');
            $value              = snmp_get($device, $value_oid, '-Oqv', 'CPQHLTH-MIB');
            $lowwarnlimit       = 0;
            $warnlimit          = snmp_get($device, $warnlimit_oid, '-Oqv', 'CPQHLTH-MIB');
            $limit              = snmp_get($device, $limit_oid, '-Oqv', 'CPQHLTH-MIB');
            $lowlimit           = 0;

            discover_sensor($valid['sensor'], 'power', $device, $value_oid, $index, 'hpe-ilo', $descr, '1', '1', $lowlimit, $low_warn_limit, $warnlimit, $limit, $value);
        }
    }//end if
}
