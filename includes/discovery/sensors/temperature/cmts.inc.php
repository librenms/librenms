<?php
$oids = snmpwalk_cache_oid_num($device, ".1.3.6.1.4.1.4998.1.1.10.1.4.2.1.29");

foreach ($oids as $index => $data) {
    $tempCurr = implode("", $data);
    if ($tempCurr !== "999") {
        $split_oid        = explode('.', $index);
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
