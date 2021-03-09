<?php
/**
 * LibreNMS - ADVA device support - Voltage Sensors
 *
 * @category   Network_Monitoring
 * @author     Christoph Zilian <czilian@hotmail.com>
 * @license    https://gnu.org/copyleft/gpl.html GNU GPL
 * @link       https://github.com/librenms/librenms/
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 **/

// ******************************************
// ***** Sensors for ADVA FSP150
// ******************************************

    // Define Sensors
    $sensors_adva = [
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.4.1.6', 'sensor_name' => 'psuOutputVoltage'],
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.7.1.5', 'sensor_name' => 'nemiVoltage'],
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.8.1.5', 'sensor_name' => 'ethernetNTUCardVoltage'],
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.9.1.5', 'sensor_name' => 'ethernetCPMRCardVoltage'],
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.10.1.5', 'sensor_name' => 'ethernetNTEGE101CardVoltage'],
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.11.1.5', 'sensor_name' => 'ethernetNTEGE206CardVoltage'],
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.14.1.5', 'sensor_name' => 'ethernetNTECardVoltage'],
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.15.1.5', 'sensor_name' => 'ethernetNTEGE201CardVoltage'],
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.16.1.5', 'sensor_name' => 'ethernetNTEGE201SyncECardVoltage'],
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.17.1.5', 'sensor_name' => 'ethernetNTEGE206FCardVoltage'],
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.25.1.5', 'sensor_name' => 'ethernetNTEGE112CardVoltage'],
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.26.1.5', 'sensor_name' => 'ethernetNTEGE114CardVoltage'],
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.27.1.5', 'sensor_name' => 'ethernetNTEGE206VCardVoltage'],
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.30.1.5', 'sensor_name' => 'ethernetNTEXG210CardVoltage'],
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.31.1.5', 'sensor_name' => 'ethernetXG1XCCCardVoltage'],
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.41.1.5', 'sensor_name' => 'ethernetGE8SCCCardVoltage'],
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.46.1.5', 'sensor_name' => 'ethernetNTEGE114SCardVoltage'],
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.57.1.5', 'sensor_name' => 'ethernetNTEGE114ProCardVoltage'],
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.80.1.5', 'sensor_name' => ' ethernetNTEXG116PROCardVoltage'],
    ];

    $multiplier = 1;
    $divisor = 1000;

    foreach (array_keys($pre_cache['adva_fsp150']) as $index) {
        foreach ($sensors_adva as $entry) {
            $sensor_name = $entry['sensor_name'];
            if ($pre_cache['adva_fsp150'][$index][$sensor_name]) {
                $oid = $entry['sensor_oid'] . '.' . $index;
                $rrd_filename = $pre_cache['adva_fsp150'][$index]['slotCardUnitName'] . '-' . $pre_cache['adva_fsp150'][$index]['slotIndex'];
                $descr = $pre_cache['adva_fsp150'][$index]['slotCardUnitName'] . ' [#' . $pre_cache['adva_fsp150'][$index]['slotIndex'] . ']';
                $current = $pre_cache['adva_fsp150'][$index][$entry['sensor_name']] / $divisor;

                discover_sensor(
                    $valid['sensor'],
                    'voltage',
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
            }//End if sensor exists
        }//End foreach $entry
    }//End foreach $index
    unset($sensors_adva, $entry);
// ************** End of Sensors for ADVA FSP150CC Series **********
