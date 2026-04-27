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
 * @copyright  2026 Network Solutions Factory
 * @author     Sofia El Khalifi <sofia.elkhalifi@netsf.fr>
 */

// Convert string with textual date to remaining days for license expiration
if (preg_match("/fgLicContractExpiry\.\d+/", (string) $sensor['sensor_index'])) {
    $expirationRaw = SnmpQuery::get($sensor['sensor_oid'])->value(0);
    $expirationDate = strtotime((string) $expirationRaw);
    $sensor_value = round((strtotime((string) $expirationRaw) - strtotime('now')) / 86400, 0);
}

if ($sensor['sensor_type'] === 'clusterState') {
    // Contains the indexes of all the cluster members
    $fgHaStatsIndex_txt = 'fgHaStatsIndex';

    // Fetch the cluster members
    $haStatsEntries = SnmpQuery::walk('FORTINET-FORTIGATE-MIB::' . $fgHaStatsIndex_txt)->table(1);

    // Count of results is the amount of cluster members
    $sensor_value = count($haStatsEntries);
}

// FortiGate stores session information as two NP forward entries for a single bidirectional firewall session.
// To derive the approximate value of NPU offloaded sessions, divide the total by half.
// This calculation has to be done in the polling module as well to ensure the value computed in the discovery is not rewritten.
if ($sensor['sensor_index'] === 'fgNPUSessionCount.0') {
    // Fetch all NPU session counts using SnmpQuery::walk
    $npuSessions = SnmpQuery::walk('FORTINET-FORTIGATE-MIB::fgNPUSessionCount')->values();

    // Sum all the values from the walk
    $total = array_sum($npuSessions);

    // Divide the total by 2
    $sensor_value = $total / 2;
}

unset(
    $expirationRaw,
    $expirationDate,
    $fgHaStatsIndex_txt,
    $haStatsEntries,
);
