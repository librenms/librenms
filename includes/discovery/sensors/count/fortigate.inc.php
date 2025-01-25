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
 * @link       https://www.librenms.org
 *
 * @copyright  2019 LibreNMS
 * @author     LibreNMS Contributors
 *
 * @copyright  2025 CTNET BV
 * @author     Rudy Broersma <r.broersma@ctnet.nl>
 */

// Sensors for license status
$licenseOids = SnmpQuery::hideMIB()->walk('FORTINET-FORTIGATE-MIB::fgSystemInfoAdvanced')->table(1);

if (! empty($licenseOids)) {
    foreach ($licenseOids as $index => $entry) {
        if (isset($entry['fgLicContractExpiry'])) {
            $descr = $entry['fgLicContractDesc'];

            // Convert the human-readable timestamp (eg: Sat Jul 26 01:00:00 2025) to unix timestamp,
            // then subtract current unix timestamp to get the remaining days till license expiration
            $sensor_value = round((strtotime($entry['fgLicContractExpiry']) - strtotime('now')) / 86400, 0);

            discover_sensor(
                null,
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
                $sensor_value,
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
    'Sessions/sec 1m avg' => ['.1.3.6.1.4.1.12356.101.4.1.11.0', 'fgSysSesRate1.0'],  //FORTINET-FORTIGATE-MIB::fgSysSesRate1.0
    'Sessions/sec 10m avg' => ['.1.3.6.1.4.1.12356.101.4.1.12.0', 'fgSysSesRate10.0'],  //FORTINET-FORTIGATE-MIB::fgSysSesRate10.0
    'Sessions/sec 30m avg' => ['.1.3.6.1.4.1.12356.101.4.1.13.0', 'fgSysSesRate30.0'],  //FORTINET-FORTIGATE-MIB::fgSysSesRate30.0
    'Sessions/sec 60m avg' => ['.1.3.6.1.4.1.12356.101.4.1.14.0', 'fgSysSesRate60.0'],  //FORTINET-FORTIGATE-MIB::fgSysSesRate60.0
    'Session count' => ['.1.3.6.1.4.1.12356.101.4.1.8.0', 'fgSysSesCount.0'],  //FORTINET-FORTIGATE-MIB::fgSysSesCount.0
];

foreach ($session_rate as $descr => $oid) {
    $oid_num = $oid[0];
    $oid_txt = $oid[1];

    $result = SnmpQuery::get('FORTINET-FORTIGATE-MIB::' . $oid_txt)->value(0);

    discover_sensor(
        null,
        'count',
        $device,
        $oid_num,
        $oid_txt,
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

// Count sensor for numbers of nodes in the cluster
$systemMode = SnmpQuery::enumStrings()->get('FORTINET-FORTIGATE-MIB::fgHaSystemMode.0')->value(0);

if ($systemMode == 'activePassive' || $systemMode == 'activeActive') {
    // Fetch the cluster members
    $haStatsEntries = SnmpQuery::walk('FORTINET-FORTIGATE-MIB::fgHaStatsIndex')->table(1);

    // Count of results is the amount of cluster members
    $clusterMemberCount = count($haStatsEntries);

    // Create a count sensor and set warning to current cluster count
    discover_sensor(
        null,
        'count',
        $device,
        '.1.3.6.1.4.1.12356.101.13.2.1.1.1',
        'fgHaStatsIndex',
        'clusterState',
        'Cluster State',
        1,
        1,
        null,
        null,
        null,
        null,
        $clusterMemberCount
    );
}

unset(
    $session_rate,
    $descr,
    $oid,
    $oid_num,
    $oid_txt,
    $result,
    $haStatsEntries,
    $clusterMemberCount
);
