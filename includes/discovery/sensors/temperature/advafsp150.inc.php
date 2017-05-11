<?php
/**
 * LibreNMS - ADVA FSP150 device support
 *
 * @category Network_Management
 * @package  LibreNMS
 * @author   Christoph Zilian <czilian@hotmail.com>
 * @license  http://gnu.org/copyleft/gpl.html GNU GPL
 * @link     https://github.com/librenms/librenms/

 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 **/
//   $sysObjectId = snmp_get($device, "SNMPv2-MIB::sysObjectID.0", "-Ovqn");


// ******************************************
// ***** Sensors for ADVA FSP150EG-X Chassis
// ******************************************

if ($device['sysObjectID'] == 'enterprises.2544.1.12.1.1.7') {
    echo 'Caching OIDs:'."\n";
    $egxPSU       = snmpwalk_cache_multi_oid($device, 'psuTable', $egxPSU, 'CM-ENTITY-MIB', '/opt/librenms/mibs/adva', '-OQUbs');
    $egxSWF       = snmpwalk_cache_multi_oid($device, 'ethernetSWFCardTable', $egxSWF, 'CM-ENTITY-MIB', '/opt/librenms/mibs/adva', '-OQUbs');
    $egxIFtemp10G = snmpwalk_cache_multi_oid($device, 'ethernet1x10GHighPerCardTable', $egxIFtemp10G, 'CM-ENTITY-MIB', '/opt/librenms/mibs/adva', '-OQUbs');
    $egxIFtemp1G  = snmpwalk_cache_multi_oid($device, 'ethernet10x1GHighPerCardTable', $egxIFtemp1G, 'CM-ENTITY-MIB', '/opt/librenms/mibs/adva', '-OQUbs');

    $multiplier = 1;
    $divisor    = 1;

    if (is_array($egxPSU)) {
        echo "psuEntry: ";

        foreach (array_keys($egxPSU) as $index) {
            $low_limit       = 10;
            $low_warn_limit  = 15;
            $high_warn_limit = 40;
            $high_limit      = 60;

            $slotnum    = $index;
            $psuname    = "PSU [".strtoupper($egxPSU[$index]['psuType'])."]";
            $descr      = $psuname." #".substr($slotnum, 4);
            $current    = $egxPSU[$index]['psuTemperature'];
            $sensorType = 'fsp150egx-psu-temp';
            $oid        = '.1.3.6.1.4.1.2544.1.12.3.1.4.1.7.'.$index;

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
    } //end of egxPSU
   
    if (is_array($egxSWF)) {
        echo "swfEntry: ";

        $multiplier = 1;
        $divisor    = 1;

        foreach (array_keys($egxSWF) as $index) {
            $low_limit       = 10;
            $low_warn_limit  = 15;
            $high_warn_limit = 60;
            $high_limit      = 80;

            $slotnum    = $index;
            $swfname    = "SWF [".$egxSWF[$index]['ethernetSWFCardOperationalState']."]";
            $descr      = $swfname." #".substr($slotnum, 4);
            $current    = $egxSWF[$index]['ethernetSWFCardTemperature'];
            $sensorType = 'fsp150egx-swf-temp';
            $oid        = '.1.3.6.1.4.1.2544.1.12.3.1.20.1.5.'.$index;

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
    } //end of egxSWF

   if (is_array($egxIFtemp10G)) {
        echo "IFtempEntry: ";

        foreach (array_keys($egxIFtemp10G) as $index) {
            $low_limit       = 10;
            $low_warn_limit  = 15;
            $high_warn_limit = 40;
            $high_limit      = 60;

            $slotnum    = $index;
            $IFname     = "IF 10G [".$egxIFtemp10G[$index]['ethernet1x10GHighPerCardOperationalState']."]";
            $descr      = $IFname." #".substr($slotnum, 4);
            $current    = $egxIFtemp10G[$index]['ethernet1x10GHighPerCardTemperature'];
            $sensorType = 'fsp150egx-if10g-temp';
            $oid        = '.1.3.6.1.4.1.2544.1.12.3.1.36.1.5.'.$index;

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
    } //end of egxIFtemp10G

  if (is_array($egxIFtemp1G)) {
        echo "IFtempEntry: ";

        foreach (array_keys($egxIFtemp1G) as $index) {
            $low_limit       = 10;
            $low_warn_limit  = 15;
            $high_warn_limit = 40;
            $high_limit      = 60;

            $slotnum    = $index;
            $IFname     = "IF 1G [".$egxIFtemp1G[$index]['ethernet10x1GHighPerCardOperationalState']."]";
            $descr      = $IFname." #".substr($slotnum, 4);
            $current    = $egxIFtemp1G[$index]['ethernet10x1GHighPerCardTemperature'];
            $sensorType = 'fsp150egx-if1g-temp';
            $oid        = '.1.3.6.1.4.1.2544.1.12.3.1.37.1.5.'.$index;

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
    } //end of egxIFtemp1G
}// *** End of ADVA FSP150EG-X Chassis


// *************************************************************
// ***** Sensors for ADVA FSP150 GE11x 114s(17) 114(9) XG210(11)
// *************************************************************

if (($device['sysObjectID'] == 'enterprises.2544.1.12.1.1.17') xor ($device['sysObjectID'] == 'enterprises.2544.1.12.1.1.9') xor ($device['sysObjectID'] == 'enterprises.2544.1.12.1.1.11')) {
    echo 'Caching OIDs:'."\n";
    $ge11x_oids   = snmpwalk_cache_multi_oid($device, 'cmEntityObjects', $ge11x_oids, 'CM-ENTITY-MIB', '/opt/librenms/mibs/adva', '-OQUbs');

    $multiplier = 1;
    $divisor    = 1;

    $ge11x_temp_sensors = array('ethernetNTEGE114CardTemperature'  => '.1.3.6.1.4.1.2544.1.12.3.1.26.1.6' ,
                                'ethernetNTEGE114SCardTemperature' => '.1.3.6.1.4.1.2544.1.12.3.1.46.1.6' ,
 				'ethernetNTEXG210CardTemperature'  => '.1.3.6.1.4.1.2544.1.12.3.1.30.1.6' ,
                                'ethernetXG1XCCCardTemperature'    => '.1.3.6.1.4.1.2544.1.12.3.1.31.1.6');

     if (is_array($ge11x_oids)) {
        echo "Temperature Sensors:\n";

        foreach (array_keys($ge11x_oids) as $index1) {
            foreach (array_keys($ge11x_temp_sensors) as $index2 => $entry) {
                if ($ge11x_oids[$index1][$entry]) {
                    $low_limit       = 10;
                    $low_warn_limit  = 15;
                    $high_warn_limit = 50;
                    $high_limit      = 60;

                    $slotnum    = $ge11x_oids[$index1]['slotIndex'];
                    $name       = $ge11x_oids[$index1]['slotCardUnitName'];
                    $descr      = $name." [Slot ".$slotnum."]";
                    $current    = $ge11x_oids[$index1][$entry];
                    $sensorType = 'advafsp150ge11x';
                    $oid        = $ge11x_temp_sensors[$entry].".".$index1;

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
    } //End if  oids exist
}// ************** End of Sensors for ADVA FSP150 GE11x 
