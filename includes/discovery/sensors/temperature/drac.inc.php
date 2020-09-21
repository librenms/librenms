<?php
/**
 * LibreNMS
 *
 *   This file is part of LibreNMS.
 *
 * @copyright  (C) 2006 - 2012 Adam Armstrong
 */
$oids = snmp_walk($device, 'temperatureProbeStatus', '-Osqn', 'IDRAC-MIB-SMIv2');
d_echo($oids . "\n");

$oids = trim($oids);
if ($oids) {
    echo 'Dell iDRAC';
}

foreach (explode("\n", $oids) as $data) {
    $data = trim($data);
    if ($data) {
        [$oid,$status] = explode(' ', $data, 2);
        d_echo('status : ' . $status . "\n");

        if ($status == 'ok') {
            $split_oid = explode('.', $oid);
            $temperature_id = $split_oid[(count($split_oid) - 2)] . '.' . $split_oid[(count($split_oid) - 1)];
            $descr_oid = ".1.3.6.1.4.1.674.10892.5.4.700.20.1.8.$temperature_id";
            $temperature_oid = ".1.3.6.1.4.1.674.10892.5.4.700.20.1.6.$temperature_id";
            $limit_oid = ".1.3.6.1.4.1.674.10892.5.4.700.20.1.10.$temperature_id";
            $warnlimit_oid = ".1.3.6.1.4.1.674.10892.5.4.700.20.1.11.$temperature_id";
            $lowwarnlimit_oid = ".1.3.6.1.4.1.674.10892.5.4.700.20.1.12.$temperature_id";
            $lowlimit_oid = ".1.3.6.1.4.1.674.10892.5.4.700.20.1.13.$temperature_id";

            $descr = trim(snmp_get($device, $descr_oid, '-Oqv', 'IDRAC-MIB-SMIv2'), '"');
            $temperature = snmp_get($device, $temperature_oid, '-Oqv', 'IDRAC-MIB-SMIv2');
            $lowwarnlimit = snmp_get($device, $lowwarnlimit_oid, '-Oqv', 'IDRAC-MIB-SMIv2');
            $warnlimit = snmp_get($device, $warnlimit_oid, '-Oqv', 'IDRAC-MIB-SMIv2');
            $limit = snmp_get($device, $limit_oid, '-Oqv', 'IDRAC-MIB-SMIv2');
            $lowlimit = snmp_get($device, $lowlimit_oid, '-Oqv', 'IDRAC-MIB-SMIv2');

            discover_sensor($valid['sensor'], 'temperature', $device, $temperature_oid, $temperature_id, 'drac', $descr, '10', '1', ($lowlimit / 10), ($low_warn_limit / 10), ($warnlimit / 10), ($limit / 10), ($temperature / 10));
        }
    }//end if
}
