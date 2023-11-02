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
 *
 * @copyright  2020 Net Entertainment AB
 * @author     Patrik Jonsson <patrik.jonsson@gmail.com>
 */
$index = 0;
$fgHaSystemMode_num = '.1.3.6.1.4.1.12356.101.13.1.1.0';
$fgHaSystemMode_txt = 'fgHaSystemMode.0';
$systemMode = snmp_get($device, $fgHaSystemMode_txt, '-Ovq', 'FORTINET-FORTIGATE-MIB');

// Verify that the device is clustered
if ($systemMode == 'activePassive' || $systemMode == 'activeActive') {
    // Indexes of all the members
    $fgHaStatsIndex_num = '.1.3.6.1.4.1.12356.101.13.2.1.1.1';
    $fgHaStatsIndex_txt = 'fgHaStatsIndex';

    // Fetch the cluster members
    $haStatsEntries = snmpwalk_cache_multi_oid($device, $fgHaStatsIndex_txt, [], 'FORTINET-FORTIGATE-MIB');

    foreach ($haStatsEntries as $index => $entry) {
        // Get name of cluster member
        $fgHaStatsHostname_txt = 'fgHaStatsHostname.' . $index;
        $cluster_member_name = snmp_get($device, $fgHaStatsHostname_txt, '-Ovq', 'FORTINET-FORTIGATE-MIB');

        // Setup a sensor for the cluster sync state
        $fgHaStatsSyncStatus_num = '.1.3.6.1.4.1.12356.101.13.2.1.1.12';
        $fgHaStatsSyncStatus_txt = 'fgHaStatsSyncStatus';
        $stateName = 'haSyncStatus';
        $descr = 'HA sync status ' . $cluster_member_name;

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
            $fgHaStatsSyncStatus_num . '.' . $index,
            $fgHaStatsSyncStatus_txt . '.' . $index,
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
    $fgHaSystemMode_num,
    $fgHaSystemMode_txt,
    $systemMode,
    $fgHaStatsIndex_num,
    $fgHaStatsIndex_txt,
    $fgHaStatsSyncStatus_num,
    $fgHaStatsSyncStatus_txt,
    $haStatsEntries,
    $stateName,
    $descr,
    $states,
    $clusterMemberCount,
    $clusterState,
    $entry
);
