<?php
/*
 * LibreNMS FortiGate count sensors
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @subpackage webui
 * @link       https://www.librenms.org
 * @copyright  2019 LibreNMS
 * @author     LibreNMS Contributors
 */

// Sensors for license status
$licenseOids = snmpwalk_cache_multi_oid($device, 'fgSystemInfoAdvanced', [], 'FORTINET-FORTIGATE-MIB');

if (! empty($licenseOids)) {
    foreach ($licenseOids as $index => $entry) {
        if (isset($entry['fgLicContractExpiry'])) {
            $descr = $entry['fgLicContractDesc'];

            discover_sensor(
                $valid['sensor'],
                'count',
                $device,
                '.1.3.6.1.4.1.12356.101.4.6.3.1.2.1.2.' . $index,
                'fgLicContractExpiry.' . $index,
                'fortigate',
                'Days left for ' . $descr,
                1,
                1,
                7,
                14,
                null,
                null,
                null,
                'snmp',
                null,
                null,
                null,
                'License expiration',
                'gauge'
            );
        }
    }
}

$session_rate = [
    'Sessions/sec 1m avg' => ['.1.3.6.1.4.1.12356.101.4.1.11', 'fgSysSesRate1'],  //FORTINET-FORTIGATE-MIB::fgSysSesRate1.0
    'Sessions/sec 10m avg' => ['.1.3.6.1.4.1.12356.101.4.1.12', 'fgSysSesRate10'],  //FORTINET-FORTIGATE-MIB::fgSysSesRate10.0
    'Sessions/sec 30m avg' => ['.1.3.6.1.4.1.12356.101.4.1.13', 'fgSysSesRate30'],  //FORTINET-FORTIGATE-MIB::fgSysSesRate30.0
    'Sessions/sec 60m avg' => ['.1.3.6.1.4.1.12356.101.4.1.14', 'fgSysSesRate60'],  //FORTINET-FORTIGATE-MIB::fgSysSesRate60.0
    'Session count' => ['.1.3.6.1.4.1.12356.101.4.1.8', 'fgSysSesCount'],  //FORTINET-FORTIGATE-MIB::fgSysSesCount.0
];

foreach ($session_rate as $descr => $oid) {
    $oid_num = $oid[0];
    $oid_txt = $oid[1];
    $result = snmp_getnext($device, $oid_txt, '-Ovq', 'FORTINET-FORTIGATE-MIB');
    $result = str_replace(' Sessions Per Second', '', $result);

    discover_sensor(
        $valid['sensor'],
        'count',
        $device,
        $oid_num . '.0',
        $oid_txt . '.0',
        'sessions',
        $descr,
        1,
        1,
        null,
        null,
        null,
        null,
        $result
    );
}

// Sensors for clusters
$fgHaSystemMode_txt = 'fgHaSystemMode.0';
$systemMode = snmp_get($device, $fgHaSystemMode_txt, '-Ovq', 'FORTINET-FORTIGATE-MIB');
if ($systemMode == 'activePassive' || $systemMode == 'activeActive') {
    // Contains the indexes of all the cluster members
    $fgHaStatsIndex_num = '.1.3.6.1.4.1.12356.101.13.2.1.1.1';
    $fgHaStatsIndex_txt = 'fgHaStatsIndex';

    // Fetch the cluster members
    $haStatsEntries = snmpwalk_cache_multi_oid($device, $fgHaStatsIndex_txt, [], 'FORTINET-FORTIGATE-MIB');

    // Count of results is the amount of cluster members
    $clusterMemberCount = count($haStatsEntries);

    // Create a count sensor and set warning to current cluster count
    discover_sensor(
        $valid['sensor'],
        'count',
        $device,
        $fgHaStatsIndex_num,
        $fgHaStatsIndex_txt,
        'clusterState',
        'Cluster State',
        1,
        1,
        null,
        $clusterMemberCount,
        null,
        null,
        $result
    );
}

unset(
    $session_rate,
    $descr,
    $oid,
    $oid_num,
    $oid_txt,
    $result,
    $fgHaSystemMode_txt,
    $fgHaStatsIndex_num,
    $fgHaStatsIndex_txt,
    $haStatsEntries,
    $clusterMemberCount
);
