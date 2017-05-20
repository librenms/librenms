<?php
/**
 * LibreNMS - ADVA device support - Current
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

// ******************************************
// ***** Sensors for ADVA FSP150EG-X Chassis
// ******************************************

if (starts_with($device['sysObjectID'], 'enterprises.2544.1.12.1.1')) {
    // Define Sensors and Limits
    $sensors = array
                (
                array(
                        'sensor_name'     => 'psuOutputCurrent',
                        'sensor_oid'      => '.1.3.6.1.4.1.2544.1.12.3.1.4.1.8',
                        'multiplier'      => 1,
                        'divisor'         => 1000,
                        'low_limit'       => 1,
                        'low_warn_limit'  => 1,
                        'high_warn_limit' => 25,
                        'high_limit'      => 30));
    
    foreach (array_keys($pre_cache['fsp150']) as $index1) {
        foreach ($sensors as $entry) {
            $sensor_name = $entry['sensor_name'];
            if ($pre_cache['fsp150'][$index1][$sensor_name]) {
                $multiplier      = $entry['multiplier'];
                $divisor         = $entry['divisor'];
                $low_limit       = $entry['low_limit'];
                $low_warn_limit  = $entry['low_warn_limit'];
                $high_warn_limit = $entry['high_warn_limit'];
                $high_limit      = $entry['high_limit'];

                $descr       = $pre_cache['fsp150'][$index1]['slotCardUnitName']." [#".$pre_cache['fsp150'][$index1]['slotIndex']."]";
                $current     = $pre_cache['fsp150'][$index1][$entry];
                $sensorType  = 'advafsp150';
                $oid         = $entry['sensor_oid'].".".$index1;

                discover_sensor(
                    $valid['sensor'],
                    'current',
                    $device,
                    $oid,
                    $index1,
                    $sensorType,
                    $descr,
                    $divisor,
                    $multiplier,
                    $low_limit,
                    $low_warn_limit,
                    $high_warn_limit,
                    $high_limit,
                    $current
                );
            }//End if sensor exists
        }//End foreach $entry
    }//End foreach $index
    unset($sensors, $entry);
}// ************** End of Sensors for ADVA FSP150CC Series **********

// *************************************************************
// ***** Sensors for ADVA FSP3000 R7
// *************************************************************

if (starts_with($device['sysObjectID'], 'enterprises.2544.1.11.1.1')) {
    $multiplier = 1;
    $divisor    = 1000;

    if (is_array($pre_cache['fsp3kr7_Card'])) {
        echo "psuEntry: ";

        foreach (array_keys($pre_cache['fsp3kr7_Card']) as $index) {
        //AC PSU Limits
            if ($pre_cache['fsp3kr7_Card'][$index]['eqptPhysInstValuePsuAmpere']) {
                if ($pre_cache['fsp3kr7_Card'][$index]['eqptPhysInstValuePsuAmpere'] > 1000) {
                    $low_limit = 210;
                    $low_warn_limit = 220;
                    $high_warn_limit = 245;
                    $high_limit = 260;
                }
        //DC PSU Limits
                $low_limit = 35;
                $low_warn_limit = 40;
                $high_warn_limit = 55;
                $high_limit = 60;

                $slotnum    = $index;
                $descr      = strtoupper($pre_cache['fsp3kr7_Card'][$index]['entityEqptAidString'])." Input";
                $current    = $pre_cachen['fsp3kr7_Card'][$index]['eqptPhysInstValuePsuAmpere'];
                $sensorType = 'advafsp3kr7';
                $oid        = '.1.3.6.1.4.1.2544.1.11.11.1.2.1.1.1.6.'.$index;

                discover_sensor(
                    $valid['sensor'],
                    'current',
                    $device,
                    $oid,
                    $index,
                    $sensorType,
                    $descr,
                    $divisor,
                    $multiplier,
                    $low_limit,
                    $low_warn_limit,
                    $high_warn_limit,
                    $high_limit,
                    $current
                );
            }
        }
    }
}// ******** End If of FSP3000 R7
