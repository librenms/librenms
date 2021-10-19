<?php
/**
 * adva_fsp150.inc.php
 *
 * -Description-
 *
 * Gather power reading from optics in Adva FSP150 series MetroE swtiches.
 * Data is pulled from the SFP by the Adva if it has Digital Optical
 * Monitoring (DOM) features. Network Ports and Access Ports have
 * different features and functions, which is why they are addressed
 * separately in the code.
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
 * Traps when Adva objects are created. This includes Remote User Login object,
 * Flow Creation object, and LAG Creation object.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2020 KanREN, Inc
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */
echo 'Adva FSP-150 dBm';

$multiplier = 1;
$divisor = 1;

// Adva Network Port dBm
foreach ($pre_cache['adva_fsp150_ports'] as $index => $entry) {
    if ($entry['cmEthernetNetPortMediaType'] == 'fiber') {
        // Discover receive power level
        $oidRx = '.1.3.6.1.4.1.2544.1.12.5.1.5.1.34.' . $index . '.3';
        $oidTx = '.1.3.6.1.4.1.2544.1.12.5.1.5.1.33.' . $index . '.3';
        $currentRx = $pre_cache['adva_fsp150_perfs'][$index . '.3']['cmEthernetNetPortStatsOPR'];
        $currentTx = $pre_cache['adva_fsp150_perfs'][$index . '.3']['cmEthernetNetPortStatsOPT'];

        if ($currentRx != 0 || $currentTx != 0) {
            $entPhysicalIndex = $entry['cmEthernetNetPortIfIndex'];
            $entPhysicalIndex_measured = 'ports';
            $descrRx = ($pre_cache['adva_fsp150_ifName'][$entry['cmEthernetNetPortIfIndex']]['ifName'] ?? 'ifIndex ' . $entry['cmEthernetNetPortIfIndex']) . ' Rx Power';
            discover_sensor(
                $valid['sensor'],
                'dbm',
                $device,
                $oidRx,
                'cmEthernetNetPortStatsOPR.' . $index,
                'adva_fsp150',
                $descrRx,
                $divisor,
                $multiplier,
                null,
                null,
                null,
                null,
                $currentRx,
                'snmp',
                $entPhysicalIndex,
                $entPhysicalIndex_measured
            );

            // Discover transmit power level
            $descrTx = ($pre_cache['adva_fsp150_ifName'][$entry['cmEthernetNetPortIfIndex']]['ifName'] ?? 'ifIndex ' . $entry['cmEthernetNetPortIfIndex']) . ' Tx Power';
            discover_sensor(
                $valid['sensor'],
                'dbm',
                $device,
                $oidTx,
                'cmEthernetNetPortStatsOPT.' . $index,
                'adva_fsp150',
                $descrTx,
                $divisor,
                $multiplier,
                null,
                null,
                null,
                null,
                $currentTx,
                'snmp',
                $entPhysicalIndex,
                $entPhysicalIndex_measured
            );
        }
    }

    // Adva Access Ports dBm
    if ($entry['cmEthernetAccPortMediaType'] == 'fiber') {
        //Discover receive power level
        $oidRx = '.1.3.6.1.4.1.2544.1.12.5.1.1.1.34.' . $index . '.3';
        $oidTx = '.1.3.6.1.4.1.2544.1.12.5.1.1.1.33.' . $index . '.3';
        $currentRx = $pre_cache['adva_fsp150_perfs'][$index . '.3']['cmEthernetAccPortStatsOPR'];
        $currentTx = $pre_cache['adva_fsp150_perfs'][$index . '.3']['cmEthernetAccPortStatsOPT'];

        if ($currentRx != 0 || $currentTx != 0) {
            $entPhysicalIndex = $entry['cmEthernetAccPortIfIndex'];
            $entPhysicalIndex_measured = 'ports';
            $descrRx = ($pre_cache['adva_fsp150_ifName'][$entry['cmEthernetAccPortIfIndex']]['ifName'] ?? 'ifIndex ' . $entry['cmEthernetAccPortIfIndex']) . ' Rx Power';

            discover_sensor(
                $valid['sensor'],
                'dbm',
                $device,
                $oidRx,
                'cmEthernetAccPortStatsOPR.' . $index,
                'adva_fsp150',
                $descrRx,
                $divisor,
                $multiplier,
                null,
                null,
                null,
                null,
                $currentRx,
                'snmp',
                $entPhysicalIndex,
                $entPhysicalIndex_measured
            );

            $descrTx = ($pre_cache['adva_fsp150_ifName'][$entry['cmEthernetAccPortIfIndex']]['ifName'] ?? 'ifIndex ' . $entry['cmEthernetAccPortIfIndex']) . ' Tx Power';

            discover_sensor(
                $valid['sensor'],
                'dbm',
                $device,
                $oidTx,
                'cmEthernetAccPortStatsOPT.' . $index,
                'adva_fsp150',
                $descrTx,
                $divisor,
                $multiplier,
                null,
                null,
                null,
                null,
                $currentTx,
                'snmp',
                $entPhysicalIndex,
                $entPhysicalIndex_measured
            );
        }
    }

    // Adva Traffic Port dBm
    if ($entry['cmEthernetTrafficPortMediaType'] == 'fiber') {
        //Discover receivn power level
        $oidRx = '.1.3.6.1.4.1.2544.1.12.5.1.21.1.34.' . $index . '.3';
        $oidTx = '.1.3.6.1.4.1.2544.1.12.5.1.21.1.33.' . $index . '.3';
        $currentRx = $pre_cache['adva_fsp150_perfs'][$index . '.3']['cmEthernetTrafficPortStatsOPR'];
        $currentTx = $pre_cache['adva_fsp150_perfs'][$index . '.3']['cmEthernetTrafficPortStatsOPT'];

        if ($currentRx != 0 || $currentTx != 0) {
            $entPhysicalIndex = $entry['cmEthernetTrafficPortIfIndex'];
            $entPhysicalIndex_measured = 'ports';
            $descrRx = ($pre_cache['adva_fsp150_ifName'][$entry['cmEthernetTrafficPortIfIndex']]['ifName'] ?? 'ifIndex ' . $entry['cmEthernetTrafficPortIfIndex']) . ' Rx Power';
            discover_sensor(
                $valid['sensor'],
                'dbm',
                $device,
                $oidRx,
                'cmEthernetTrafficPortStatsOPR.' . $index,
                'adva_fsp150',
                $descrRx,
                $divisor,
                $multiplier,
                null,
                null,
                null,
                null,
                $currentRx,
                'snmp',
                $entPhysicalIndex,
                $entPhysicalIndex_measured
            );

            $descrTx = ($pre_cache['adva_fsp150_ifName'][$entry['cmEthernetTrafficPortIfIndex']]['ifName'] ?? 'ifIndex ' . $entry['cmEthernetTrafficPortIfIndex']) . ' Tx Power';
            discover_sensor(
                $valid['sensor'],
                'dbm',
                $device,
                $oidTx,
                'cmEthernetTrafficPortStatsOPT.' . $index,
                'adva_fsp150',
                $descrTx,
                $divisor,
                $multiplier,
                null,
                null,
                null,
                null,
                $currentTx,
                'snmp',
                $entPhysicalIndex,
                $entPhysicalIndex_measured
            );
        }
    }
}

unset($entry);
