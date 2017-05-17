<?php

$oids = snmp_walk($device, ".1.3.6.1.4.1.4998.1.1.10.1.4.2.1.29", "-Osqn", "");
$oids = trim($oids);
$oids = explode("\n", $oids);
print_r($oids);

foreach ($oids as $data) {
    $data = trim($data);
    if ($data) {
        list($oid, $tempCurr) = explode(" ", $data);
        if ($tempCurr !== "999") {
            $tempCurr         = trim($tempCurr, ".");
            $split_oid        = explode('.', $oid);
            $temperature_id   = $split_oid[(count($split_oid) - 1)];
            $temperature_oid  = ".1.3.6.1.4.1.4998.1.1.10.1.4.2.1.29.1.$temperature_id";
            $descr_oid        = ".1.3.6.1.4.1.4998.1.1.10.1.4.2.1.3.1.$temperature_id";
            $warnlimit_oid    = ".1.3.6.1.4.1.4998.1.1.10.1.4.2.1.44.1.$temperature_id";
            $limit_oid        = ".1.3.6.1.4.1.4998.1.1.10.1.4.2.1.45.1.$temperature_id";

            $descr            = trim(snmp_get($device, $descr_oid, '-Oqv', ''), '"');
            $warnlimit        = snmp_get($device, $warnlimit_oid, '-Oqv', '');
            $limit            = snmp_get($device, $limit_oid, '-Oqv', '');

            discover_sensor($valid['sensor'], 'temperature', $device, $temperature_oid, $temperature_id, 'cmts', $descr, '1', '1', null, null, $warnlimit, $limit, $tempCurr);
        }
    }
}
