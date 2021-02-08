<?php
/**
 * fortigate.inc.php
 *
 * LibreNMS state sensor state polling module for Fortigate Firewalls
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
if ($device['os'] == 'fortigate') {
    if (in_array($sensor['sensor_type'], ['clusterState', 'haSyncStatus'])) {
        // Fetch the cluster members and their data
        $fgHaStatsEntryOid = 'fgHaStatsEntry';
        $haStatsEntries = snmpwalk_cache_multi_oid($device, $fgHaStatsEntryOid, [], 'FORTINET-FORTIGATE-MIB');

        if ($sensor['sensor_type'] == 'clusterState') {
            // Determine if the cluster contains more than 1 device
            $clusterState = 0;
            if (is_array($haStatsEntries)) {
                $clusterMemberCount = count($haStatsEntries);
                $clusterState = $clusterMemberCount == 1 ? 0 : 1;
            }
            $sensor_value = $clusterState;
        } elseif ($sensor['sensor_type'] == 'haSyncStatus') {
            // 0 = Out of sync, 1 = In Sync, 2 = No Peer
            $synchronized = 1;

            $clusterMemberCount = count($haStatsEntries);
            if ($clusterMemberCount == 1) {
                $synchronized = 2;
            } else {
                foreach ($haStatsEntries as $entry) {
                    if ($entry['fgHaStatsSyncStatus'] == 'unsynchronized') {
                        $synchronized = 0;
                    }
                }
            }
            $sensor_value = $synchronized;
        }

        unset($fgHaStatsEntryOid, $haStatsEntries, $clusterMemberCount, $synchronized, $clusterState, $clusterMemberCount);
    }
}
