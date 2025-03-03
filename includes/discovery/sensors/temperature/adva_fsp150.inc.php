<?php
/**
 * LibreNMS - ADVA device support - Temperature Sensors
 *
 * @category   Network_Monitoring
 *
 * @author     Christoph Zilian <czilian@hotmail.com>
 * @license    https://gnu.org/copyleft/gpl.html GNU GPL
 *
 * @link       https://github.com/librenms/librenms/
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 **/

// *************************************************************
// ***** Temperature Sensors for ADVA FSP150CC Series
// *************************************************************

$sensors_adva = [
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.4.1.7', 'sensor_name' => 'psuTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.6.1.6', 'sensor_name' => 'scuTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.7.1.6', 'sensor_name' => 'nemiTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.8.1.6', 'sensor_name' => 'ethernetNTUCardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.9.1.6', 'sensor_name' => 'ethernetCPMRCardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.10.1.6', 'sensor_name' => 'ethernetNTEGE101CardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.11.1.6', 'sensor_name' => 'ethernetNTEGE206CardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.13.1.6', 'sensor_name' => 'scuTTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.14.1.6', 'sensor_name' => 'ethernetNTECardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.15.1.6', 'sensor_name' => 'ethernetNTEGE201CardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.16.1.6', 'sensor_name' => 'ethernetNTEGE201SyncECardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.17.1.6', 'sensor_name' => 'ethernetNTEGE206FCardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.18.1.5', 'sensor_name' => 'ethernet1x10GCardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.19.1.5', 'sensor_name' => 'ethernet10x1GCardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.20.1.5', 'sensor_name' => 'ethernetSWFCardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.21.1.5', 'sensor_name' => 'stuCardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.22.1.5', 'sensor_name' => 'amiTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.23.1.5', 'sensor_name' => 'stiTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.25.1.6', 'sensor_name' => 'ethernetNTEGE112CardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.26.1.6', 'sensor_name' => 'ethernetNTEGE114CardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.27.1.6', 'sensor_name' => 'ethernetNTEGE206VCardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.28.1.6', 'sensor_name' => 'ethernetGE4SCCCardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.29.1.6', 'sensor_name' => 'ethernetGE4ECCCardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.30.1.6', 'sensor_name' => 'ethernetNTEXG210CardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.31.1.6', 'sensor_name' => 'ethernetXG1XCCCardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.32.1.6', 'sensor_name' => 'ethernetXG1SCCCardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.33.1.5', 'sensor_name' => 'ethernetOverOCSTMCardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.36.1.5', 'sensor_name' => 'ethernet1x10GHighPerCardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.37.1.5', 'sensor_name' => 'ethernet10x1GHighPerCardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.38.1.6', 'sensor_name' => 'ethernetNTET1804CardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.41.1.6', 'sensor_name' => 'ethernetGE8SCCCardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.42.1.6', 'sensor_name' => 'ethernetNTEGE114HCardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.43.1.6', 'sensor_name' => 'ethernetNTEGE114PHCardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.45.1.6', 'sensor_name' => 'ethernetNTEGE114SHCardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.46.1.6', 'sensor_name' => 'ethernetNTEGE114SCardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.47.1.5', 'sensor_name' => 'stuHighPerCardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.48.1.5', 'sensor_name' => 'stiHighPerTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.49.1.6', 'sensor_name' => 'ethernetGE8ECCCardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.53.1.6', 'sensor_name' => 'ethernetNTEGE112ProCardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.54.1.6', 'sensor_name' => 'ethernetNTEGE112ProMCardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.55.1.6', 'sensor_name' => 'ethernetNTEXG210CCardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.56.1.6', 'sensor_name' => 'ethernetGE8SCryptoConnectorCardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.57.1.6', 'sensor_name' => 'ethernetNTEGE114ProCardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.58.1.6', 'sensor_name' => 'ethernetNTEGE114ProCCardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.59.1.6', 'sensor_name' => 'ethernetNTEGE114ProSHCardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.60.1.6', 'sensor_name' => 'ethernetNTEGE114ProCSHCardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.61.1.6', 'sensor_name' => 'ethernetNTEGE114ProHECardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.62.1.6', 'sensor_name' => 'ethernetNTEGE112ProHCardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.65.1.6', 'sensor_name' => 'ethernetNTEGE114GCardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.67.1.6', 'sensor_name' => 'ethernetNTEGE114ProVmHCardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.68.1.6', 'sensor_name' => 'ethernetNTEGE114ProVmCHCardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.69.1.6', 'sensor_name' => 'ethernetNTEGE114ProVmCSHCardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.70.1.7', 'sensor_name' => 'serverCardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.80.1.6', 'sensor_name' => 'ethernetNTEXG116PROCardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.81.1.6', 'sensor_name' => 'ethernetNTEXG120PROCardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.91.1.6', 'sensor_name' => 'ethernetNTEXG116PROHCardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.93.1.6', 'sensor_name' => 'ethernetNTEXG118PROSHCardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.94.1.6', 'sensor_name' => 'ethernetNTEXG118PROACSHCardTemperature'],
    ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.97.1.6', 'sensor_name' => 'ethernetNTEXG120PROSHCardTemperature'],
];

$multiplier = 1;
$divisor = 1;

foreach (array_keys($pre_cache['adva_fsp150']) as $index) {
    foreach ($sensors_adva as $entry) {
        $sensor_name = $entry['sensor_name'];
        if (! empty($pre_cache['adva_fsp150'][$index][$sensor_name])) {
            $oid = $entry['sensor_oid'] . '.' . $index;
            $descr = $pre_cache['adva_fsp150'][$index]['slotCardUnitName'] . ' [#' . $pre_cache['adva_fsp150'][$index]['slotIndex'] . ']';
            $current = $pre_cache['adva_fsp150'][$index][$entry['sensor_name']] / $divisor;

            d_echo($pre_cache['adva_fsp150']);
            discover_sensor(
                null,
                'temperature',
                $device,
                $oid,
                $entry['sensor_name'] . $index,
                'adva_fsp150',
                $descr,
                $divisor,
                $multiplier,
                null,
                null,
                null,
                null,
                $current
            );
        } //End if sensor exists
    } //End foreach $entry
} //End foreach $index
unset($sensors_adva, $entry);
// ************** End of Sensors for ADVA FSP150CC Series **********

// Adva FSP150 SFP DOM Temperature

foreach ($pre_cache['adva_fsp150_ports'] as $index => $entry) {
    if (isset($entry['cmEthernetNetPortMediaType']) && $entry['cmEthernetNetPortMediaType'] == 'fiber' && $entry['cmEthernetNetPortOperationalState'] == 'normal') {
        $oid = '.1.3.6.1.4.1.2544.1.12.5.1.5.1.40.' . $index . '.3';
        $current = $pre_cache['adva_fsp150_perfs'][$index . '.3']['cmEthernetNetPortStatsTemp'];

        if ($current != 0) {
            $entPhysicalIndex = $entry['cmEthernetNetPortIfIndex'];
            $entPhysicalIndex_measured = 'ports';
            $descr = ($pre_cache['adva_fsp150_ifName'][$entry['cmEthernetNetPortIfIndex']]['ifName'] ?? 'ifIndex ' . $entry['cmEthernetNetPortIfIndex']);
            discover_sensor(
                null,
                'temperature',
                $device,
                $oid,
                'cmEthernetNetPortStatsTemp.' . $index,
                'adva_fsp150',
                $descr,
                $divisor,
                $multiplier,
                null,
                null,
                null,
                null,
                $current,
                'snmp',
                $entPhysicalIndex,
                $entPhysicalIndex_measured
            );
        }
    }

    if (isset($entry['cmEthernetAccPortMediaType']) && $entry['cmEthernetAccPortMediaType'] == 'fiber' && $entry['cmEthernetAccPortOperationalState'] == 'normal') {
        $oid = '.1.3.6.1.4.1.2544.1.12.5.1.1.1.39.' . $index . '.3';
        $current = $pre_cache['adva_fsp150_perfs'][$index . '.3']['cmEthernetAccPortStatsTemp'] ?? null;
        if ($current != 0) {
            $entPhysicalIndex = $entry['cmEthernetAccPortIfIndex'];
            $entPhysicalIndex_measured = 'ports';
            $descr = ($pre_cache['adva_fsp150_ifName'][$entry['cmEthernetAccPortIfIndex']]['ifName'] ?? 'ifIndex ' . $entry['cmEthernetAccPortIfIndex']);
            discover_sensor(
                null,
                'temperature',
                $device,
                $oid,
                'cmEthernetAccPortStatsTemp.' . $index,
                'adva_fsp150',
                $descr,
                $divisor,
                $multiplier,
                null,
                null,
                null,
                null,
                $current,
                'snmp',
                $entPhysicalIndex,
                $entPhysicalIndex_measured
            );
        }
    }

    if (isset($entry['cmEthernetTrafficPortMediaType']) && $entry['cmEthernetTrafficPortMediaType'] == 'fiber' && $entry['cmEthernetTrafficPortOperationalState'] == 'normal') {
        $oid = '.1.3.6.1.4.1.2544.1.12.5.1.21.1.41.' . $index . '.3';
        $current = $pre_cache['adva_fsp150_perfs'][$index . '.3']['cmEthernetTrafficPortStatsTemp'] ?? null;
        if ($current != 0) {
            $entPhysicalIndex = $entry['cmEthernetTrafficPortIfIndex'];
            $entPhysicalIndex_measured = 'ports';
            $descr = ($pre_cache['adva_fsp150_ifName'][$entry['cmEthernetTrafficPortIfIndex']]['ifName'] ?? 'ifIndex ' . $entry['cmEthernetTrafficPortIfIndex']);
            discover_sensor(
                null,
                'temperature',
                $device,
                $oid,
                'cmEthernetTrafficPortStatsTemp.' . $index,
                'adva_fsp150',
                $descr,
                $divisor,
                $multiplier,
                null,
                null,
                null,
                null,
                $current,
                'snmp',
                $entPhysicalIndex,
                $entPhysicalIndex_measured
            );
        }
    }
}
