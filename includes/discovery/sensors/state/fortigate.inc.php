<?php
/**
 * fortigate.inc.php
 *
 * LibreNMS state sensor discovery module for Fortigate Firewalls
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2020 Net Entertainment AB
 * @author     Patrik Jonsson <patrik.jonsson@gmail.com>
 */
$index = 0;
$fgHaSystemModeOid = 'fgHaSystemMode.0';
$systemMode = snmp_get($device, $fgHaSystemModeOid, '-Ovq', 'FORTINET-FORTIGATE-MIB');

// Verify that the device is clustered
if ($systemMode == 'activePassive' || $systemMode == 'activeActive') {
    $fgHaStatsEntryOid = 'fgHaStatsEntry';

    // Fetch the cluster members
    $haStatsEntries = snmpwalk_cache_multi_oid($device, $fgHaStatsEntryOid, [], 'FORTINET-FORTIGATE-MIB');

    if (is_array($haStatsEntries)) {
        $stateName = 'clusterState';
        $descr = 'Cluster State';

        $states = [
            ['value' => 0, 'generic' => 2, 'graph' => 0, 'descr' => 'CRITICAL'],
            ['value' => 1, 'generic' => 0, 'graph' => 1, 'descr' => 'OK'],
        ];

        create_state_index($stateName, $states);

        $clusterMemberCount = count($haStatsEntries);

        // If the device is part of a cluster but the member count is 1 the cluster has issues
        $clusterState = $clusterMemberCount == 1 ? 0 : 1;

        discover_sensor(
            $valid['sensor'],
            'state',
            $device,
            $fgHaSystemModeOid,
            $index,
            $stateName,
            $descr,
            1,
            1,
            null,
            null,
            null,
            null,
            $clusterState,
            'snmp',
            null,
            null,
            null,
            'HA'
        );

        create_sensor_to_state_index($device, $stateName, $index);

        // Setup a sensor for the cluster sync state
        $stateName = 'haSyncStatus';
        $descr = 'HA sync status';
        $states = [
            ['value' => 0, 'generic' => 2, 'graph' => 0, 'descr' => 'Out Of Sync'],
            ['value' => 1, 'generic' => 0, 'graph' => 1, 'descr' => 'In Sync'],
            ['value' => 2, 'generic' => 1, 'graph' => 0, 'descr' => 'No Peer'],
        ];

        create_state_index($stateName, $states);

        discover_sensor(
            $valid['sensor'],
            'state',
            $device,
            $fgHaStatsEntryOid,
            $index,
            $stateName,
            $descr,
            1,
            1,
            null,
            null,
            null,
            null,
            1,
            'snmp',
            $index,
            null,
            null,
            'HA'
        );

        create_sensor_to_state_index($device, $stateName, $index);
    }
}

unset(
    $index,
    $fgHaSystemModeOid,
    $systemMode,
    $fgHaStatsEntryOid,
    $haStatsEntries,
    $stateName,
    $descr,
    $states,
    $clusterMemberCount,
    $clusterState,
    $entry
);
