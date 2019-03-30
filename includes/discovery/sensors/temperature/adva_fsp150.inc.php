<?php
/**
 * LibreNMS - ADVA device support - Temperature Sensors
 *
 * @category   Network_Monitoring
 * @package    LibreNMS
 * @subpackage ADVA device support
 * @author     Christoph Zilian <czilian@hotmail.com>
 * @license    http://gnu.org/copyleft/gpl.html GNU GPL
 * @link       https://github.com/librenms/librenms/

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
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.25.1.6', 'sensor_name' => 'ethernetNTEGE112CardTemperature'],
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.26.1.6', 'sensor_name' => 'ethernetNTEGE114CardTemperature'],
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.46.1.6', 'sensor_name' => 'ethernetNTEGE114SCardTemperature'],
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.30.1.6', 'sensor_name' => 'ethernetNTEXG210CardTemperature'],
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.31.1.6', 'sensor_name' => 'ethernetXG1XCCCardTemperature'],
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.37.1.5', 'sensor_name' => 'ethernet10x1GHighPerCardTemperature'],
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.36.1.5', 'sensor_name' => 'ethernet1x10GHighPerCardTemperature'],
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.20.1.5', 'sensor_name' => 'ethernetSWFCardTemperature'],
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.4.1.7', 'sensor_name' => 'psuTemperature'],
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.6.1.6', 'sensor_name' => 'scuTemperature'],
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.7.1.6', 'sensor_name' => 'nemiTemperature'],
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.9.1.6', 'sensor_name' => 'ethernetCPMRCardTemperature'],
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.10.1.6', 'sensor_name' => 'ethernetNTEGE101CardTemperature'],
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.11.1.6', 'sensor_name' => 'ethernetNTEGE206CardTemperature'],
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.14.1.6', 'sensor_name' => 'ethernetNTECardTemperature'],
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.15.1.6', 'sensor_name' => 'ethernetNTEGE201CardTemperature'],
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.16.1.6', 'sensor_name' => 'ethernetNTEGE201SyncECardTemperature'],
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.17.1.6', 'sensor_name' => 'ethernetNTEGE206FCardTemperature'],
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.18.1.6', 'sensor_name' => 'ethernet1x10GCardTemperature'],
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.19.1.6', 'sensor_name' => 'ethernet10x1GCardTemperature'],
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.21.1.6', 'sensor_name' => 'stuCardTemperature'],
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.22.1.5', 'sensor_name' => 'amiTemperature'],
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.41.1.6', 'sensor_name' => 'ethernetGE8SCCCardTemperature'],
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.47.1.5', 'sensor_name' => 'stuHighPerCardTemperature'],
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.48.1.5', 'sensor_name' => 'stiHighPerTemperature'],
        ['sensor_oid' => '.1.3.6.1.4.1.2544.1.12.3.1.49.1.6', 'sensor_name' => 'ethernetGE8ECCCardTemperature'],
    ];

    $multiplier = 1;
    $divisor    = 1;
  
    foreach (array_keys($pre_cache['adva_fsp150']) as $index) {
        foreach ($sensors_adva as $entry) {
            $sensor_name = $entry['sensor_name'];
            if ($pre_cache['adva_fsp150'][$index][$sensor_name]) {
                $oid          = $entry['sensor_oid'].".".$index;
                $descr        = $pre_cache['adva_fsp150'][$index]['slotCardUnitName']." [#".$pre_cache['adva_fsp150'][$index]['slotIndex']."]";
                $current      = $pre_cache['adva_fsp150'][$index][$entry['sensor_name']]/$divisor;

                d_echo($pre_cache['adva_fsp150']);
                discover_sensor(
                    $valid['sensor'],
                    'temperature',
                    $device,
                    $oid,
                    $entry['sensor_name'].$index,
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
