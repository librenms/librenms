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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @link       http://librenms.org
 * @copyright  2020 Net Entertainment AB
 * @author     Patrik Jonsson <patrik.jonsson@gmail.com>
 */
$index = 0;
$fgHaSystemModeOid = '.1.3.6.1.4.1.12356.101.13.1.1.0';
$systemMode = snmp_get($device, $fgHaSystemModeOid, '-Ovq', 'FORTINET-FORTIGATE-MIB');

// Verify that there is a cluster in the first place
if ($systemMode == 'activePassive' || $systemMode == 'activeActive') {
    $fgHaStatsEntryOid = '.1.3.6.1.4.1.12356.101.13.2.1.1';

    // Fetch the cluster members
    $haStats = snmpwalk_cache_multi_oid($device, $fgHaStatsEntryOid, [], 'FORTINET-FORTIGATE-MIB');

    if (is_array($haStats)) {
        $stateName = 'clusterState';
        $states = [
            ['value' => 0, 'generic' => 2, 'graph' => 0, 'descr' => 'CRITICAL'],
            ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'OK'],
        ];

        create_state_index($stateName, $states);

        $clusterMemberCount = count($haStats);
        $clusterState = $clusterMemberCount == 1 ? 0 : 1;

        $descr = 'Cluster State';
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
            $clusterState,
            'snmp',
            null,
            null,
            null,
            'HA'
        );

        create_sensor_to_state_index($device, $stateName, $index);
    }
}
