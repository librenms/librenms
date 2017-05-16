<?php
/**
 * LibreNMS - ADVA device support - Voltage Sensors
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

if ($device['sysObjectID'] == 'enterprises.2544.1.12.1.1.7') {

    $multiplier = 1;
    $divisor    = 1000;

    if (is_array($pre_cache['egxPSU'])) {
        foreach (array_keys($pre_cache['egxPSU']) as $index) {

            $low_limit = 6;
            $low_warn_limit = 9;
            $high_warn_limit = 14;
            $high_limit = 20;

            $slotnum    = substr($index, 4);
            $psuname    = "PSU[".strtoupper($pre_cache['egxPSU'][$index]['psuType'])."]";
            $descr      = $psuname." #".$slotnum.' DC Output';
            $current    = $pre_cache['egxPSU'][$index]['psuOutputVoltage'];
            $sensorType = 'fsp150egxOutputVoltage';
            $oid        = '.1.3.6.1.4.1.2544.1.12.3.1.4.1.6.'.$index;

            discover_sensor(
                $valid['sensor'],
                'voltage',
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
}// *****  End If of FSP150EG-X


// *************************************************************
// ***** Sensors for ADVA FSP150 GE11x 114s(17) 114(9) XG210(11)
// *************************************************************

if (($device['sysObjectID'] == 'enterprises.2544.1.12.1.1.17') xor ($device['sysObjectID'] == 'enterprises.2544.1.12.1.1.9') xor ($device['sysObjectID'] == 'enterprises.2544.1.12.1.1.11')) {
    echo 'Caching OIDs:'."\n";
    $sensor_oids   = snmpwalk_cache_multi_oid($device, 'cmEntityObjects', $sensor_oids, 'CM-ENTITY-MIB', '/opt/librenms/mibs/adva', '-OQUbs');

    $multiplier = 1;
    $divisor    = 1000;

    $temp_sensors = array('ethernetNTEGE114CardVoltage'  => '.1.3.6.1.4.1.2544.1.12.3.1.26.1.5' ,
                                'ethernetNTEGE114SCardVoltage' => '.1.3.6.1.4.1.2544.1.12.3.1.46.1.5' ,
                                'ethernetNTEXG210CardVoltage'  => '.1.3.6.1.4.1.2544.1.12.3.1.30.1.5');
// Laser Voltage                'ethernetXG1XCCCardVoltage'    => '.1.3.6.1.4.1.2544.1.12.3.1.31.1.5');

    if (is_array($sensor_oids)) {
        echo "Temperature Sensors:\n";
        foreach (array_keys($sensor_oids) as $index1) {
            foreach (array_keys($temp_sensors) as $index2 => $entry) {
                if ($sensor_oids[$index1][$entry]) {
                    $low_limit       = 9;
                    $low_warn_limit  = 10;
                    $high_warn_limit = 14;
                    $high_limit      = 15;

                    $slotnum    = $sensor_oids[$index1]['slotIndex'];
                    $name       = $sensor_oids[$index1]['slotCardUnitName'];
                    $descr      = $name." [Slot ".$slotnum."]";
                    $current    = $sensor_oids[$index1][$entry];
                    $sensorType = 'fsp150voltage';
                    $oid        = $temp_sensors[$entry].".".$index1;

                    discover_sensor(
                        $valid['sensor'],
                        'voltage',
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
    } //End if  oids exist
}// ************** End of Sensors for ADVA FSP150 GE11x **********

// *************************************************************
// ***** Sensors for ADVA FSP3000 R7
// *************************************************************

if ($device['sysObjectID'] == 'enterprises.2544.1.11.1.1') {
    echo 'Caching OIDs:'."\n";
    $fsp3kr7_Card = snmpwalk_cache_multi_oid($device, 'eqptPhysInstValueEntry', $fsp3kr7_Card, 'ADVA-FSPR7-PM-MIB', '/opt/librenms/mibs/adva', '-OQUbs');

    $multiplier = 1;
    $divisor    = 1000;

    if (is_array($fsp3kr7_Card)) {
        echo "psuEntry: ";

        foreach (array_keys($fsp3kr7_Card) as $index) {
        //AC PSU Limits
            if ($fsp3kr7_Card[$index]['eqptPhysInstValuePsuVoltInp']) {
                if ($fsp3kr7_Card[$index]['eqptPhysInstValuePsuVoltInp'] > 1000) {
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
                $descr      = strtoupper($fsp3kr7_Card[$index]['entityEqptAidString'])." Input";
                $current    = $fsp3kr7_Card[$index]['eqptPhysInstValuePsuVoltInp'];
                $sensorType = 'fsp3kr7psuInputV';
                $oid        = '.1.3.6.1.4.1.2544.1.11.11.1.2.1.1.1.7.'.$index;

                discover_sensor(
                    $valid['sensor'],
                    'voltage',
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
