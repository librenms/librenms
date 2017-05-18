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

if (starts_with($device['sysObjectID'], 'enterprises.2544.1.12.1.1')) {
    // Define Sensors and Limits
    $sensors = array
                (
                array(
                        'sensor_name'     => 'ethernetNTEGE114CardTemperature',
                        'sensor_oid'      => '.1.3.6.1.4.1.2544.1.12.3.1.26.1.6',
                        'multiplier'      => 1,
                        'divisor'         => 1,
                        'low_limit'       => 15,
                        'low_warn_limit'  => 20,
                        'high_warn_limit' => 50,
                        'high_limit'      => 60),
                array(
                        'sensor_name'     => 'ethernetNTEGE114SCardTemperature',
                        'sensor_oid'      => '.1.3.6.1.4.1.2544.1.12.3.1.46.1.6',
                        'multiplier'      => 1,
                        'divisor'         => 1,
                        'low_limit'       => 15,
                        'low_warn_limit'  => 20,
                        'high_warn_limit' => 50,
                        'high_limit'      => 60),
                array(
                        'sensor_name'     => 'ethernetNTEXG210CardTemperature',
                        'sensor_oid'      => '.1.3.6.1.4.1.2544.1.12.3.1.30.1.6',
                        'multiplier'      => 1,
                        'divisor'         => 1,
                        'low_limit'       => 15,
                        'low_warn_limit'  => 20,
                        'high_warn_limit' => 50,
                        'high_limit'      => 60),
                array(
                        'sensor_name'     => 'ethernetXG1XCCCardTemperature',
                        'sensor_oid'      => '.1.3.6.1.4.1.2544.1.12.3.1.31.1.6',
                        'multiplier'      => 1,
                        'divisor'         => 1,
                        'low_limit'       => 15,
                        'low_warn_limit'  => 20,
                        'high_warn_limit' => 50,
                        'high_limit'      => 60),
                array(
                        'sensor_name'     => 'ethernet10x1GHighPerCardTemperature',
                        'sensor_oid'      => '.1.3.6.1.4.1.2544.1.12.3.1.37.1.5',
                        'multiplier'      => 1,
                        'divisor'         => 1,
                        'low_limit'       => 15,
                        'low_warn_limit'  => 20,
                        'high_warn_limit' => 50,
                        'high_limit'      => 60),
                array(
                        'sensor_name'     => 'ethernet1x10GHighPerCardTemperature',
                        'sensor_oid'      => '.1.3.6.1.4.1.2544.1.12.3.1.36.1.5',
                        'multiplier'      => 1,
                        'divisor'         => 1,
                        'low_limit'       => 15,
                        'low_warn_limit'  => 20,
                        'high_warn_limit' => 50,
                        'high_limit'      => 60),
                array(
                        'sensor_name'     => 'ethernetSWFCardTemperature',
                        'sensor_oid'      => '.1.3.6.1.4.1.2544.1.12.3.1.20.1.5',
                        'multiplier'      => 1,
                        'divisor'         => 1,
                        'low_limit'       => 15,
                        'low_warn_limit'  => 20,
                        'high_warn_limit' => 50,
                        'high_limit'      => 60),
                array(
                        'sensor_name'     => 'psuTemperature',
                        'sensor_oid'      => '.1.3.6.1.4.1.2544.1.12.3.1.4.1.7',
                        'multiplier'      => 1,
                        'divisor'         => 1,
                        'low_limit'       => 15,
                        'low_warn_limit'  => 20,
                        'high_warn_limit' => 50,
                        'high_limit'      => 60),
                array(
                        'sensor_name'     => 'scuTemperature',
                        'sensor_oid'      => '.1.3.6.1.4.1.2544.1.12.3.1.6.1.6',
                        'multiplier'      => 1,
                        'divisor'         => 1,
                        'low_limit'       => 15,
                        'low_warn_limit'  => 20,
                        'high_warn_limit' => 50,
                        'high_limit'      => 60),
                array(
                        'sensor_name'     => 'nemiTemperature',
                        'sensor_oid'      => '.1.3.6.1.4.1.2544.1.12.3.1.7.1.6',
                        'multiplier'      => 1,
                        'divisor'         => 1,
                        'low_limit'       => 15,
                        'low_warn_limit'  => 20,
                        'high_warn_limit' => 50,
                        'high_limit'      => 60),
                array(
                        'sensor_name'     => 'amiTemperature',
                        'sensor_oid'      => '.1.3.6.1.4.1.2544.1.12.3.1.22.1.5',
                        'multiplier'      => 1,
                        'divisor'         => 1,
                        'low_limit'       => 15,
                        'low_warn_limit'  => 20,
                        'high_warn_limit' => 50,
                        'high_limit'      => 60),
                array(
                        'sensor_name'     => 'ethernetGE8SCCCardTemperature',
                        'sensor_oid'      => '.1.3.6.1.4.1.2544.1.12.3.1.41.1.6',
                        'multiplier'      => 1,
                        'divisor'         => 1,
                        'low_limit'       => 15,
                        'low_warn_limit'  => 20,
                        'high_warn_limit' => 50,
                        'high_limit'      => 60),
                array(
                        'sensor_name'     => 'stuHighPerCardTemperature',
                        'sensor_oid'      => '.1.3.6.1.4.1.2544.1.12.3.1.47.1.5',
                        'multiplier'      => 1,
                        'divisor'         => 1,
                        'low_limit'       => 15,
                        'low_warn_limit'  => 20,
                        'high_warn_limit' => 50,
                        'high_limit'      => 60),
                array(
                        'sensor_name'     => 'stiHighPerTemperature',
                        'sensor_oid'      => '.1.3.6.1.4.1.2544.1.12.3.1.48.1.5',
                        'multiplier'      => 1,
                        'divisor'         => 1,
                        'low_limit'       => 15,
                        'low_warn_limit'  => 20,
                        'high_warn_limit' => 50,
                        'high_limit'      => 60),
                array(
                        'sensor_name'     => 'ethernetGE8ECCCardTemperature',
                        'sensor_oid'      => '.1.3.6.1.4.1.2544.1.12.3.1.49.1.6',
                        'multiplier'      => 1,
                        'divisor'         => 1,
                        'low_limit'       => 15,
                        'low_warn_limit'  => 20,
                        'high_warn_limit' => 50,
                        'high_limit'      => 60));

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
                    'temperature',
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
// ***** Temperature Sensors for ADVA FSP3000 R7
// *************************************************************

if (starts_with($device['sysObjectID'], 'enterprises.2544.1.11.1.1')) {
    $multiplier = 1;
    $divisor    = 10;

    if (is_array($pre_cache['fsp3kr7_Card'])) {
        foreach (array_keys($pre_cache['fsp3kr7_Card']) as $index) {
            if ($pre_cache['fsp3kr7_Card'][$index]['eqptPhysInstValueTemp']) {
                $low_limit = 10;
                $low_warn_limit = 15;
                $high_warn_limit = 35;
                $high_limit = $pre_cache['fsp3kr7_Card'][$index]['eqptPhysThresholdTempHigh']/$divisor;

                $slotnum    = $index;
                $descr      = $pre_cache['fsp3kr7_Card'][$index]['entityEqptAidString'];
                $current    = $pre_cache['fsp3kr7_Card'][$index]['eqptPhysInstValueTemp'];
                $sensorType = 'advafsp3kr7';
                $oid        = '.1.3.6.1.4.1.2544.1.11.11.1.2.1.1.1.5.'.$index;

                discover_sensor(
                    $valid['sensor'],
                    'temperature',
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
}//  ************** End of Sensors for ADVA FSP3000 R7 **********
