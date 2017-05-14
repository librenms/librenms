<?php
/**
 * LibreNMS - ADVA device support
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
    $sensor_oids   = snmpwalk_cache_multi_oid($device, 'cmEntityObjects', $sensor_oids, 'CM-ENTITY-MIB', '/opt/librenms/mibs/adva', '-OQUbs');

    $multiplier = 1;
    $divisor    = 1;

    $temp_sensors = array('ethernetNTEGE114CardTemperature'  => '.1.3.6.1.4.1.2544.1.12.3.1.26.1.6' ,
                                'ethernetNTEGE114SCardTemperature' => '.1.3.6.1.4.1.2544.1.12.3.1.46.1.6' ,
 				'ethernetNTEXG210CardTemperature'  => '.1.3.6.1.4.1.2544.1.12.3.1.30.1.6' ,
                                'ethernetXG1XCCCardTemperature'    => '.1.3.6.1.4.1.2544.1.12.3.1.31.1.6');

     if (is_array($sensor_oids)) {
        echo "Temperature Sensors:\n";
        foreach (array_keys($sensor_oids) as $index1) {
            foreach (array_keys($temp_sensors) as $index2 => $entry) {
                if ($sensor_oids[$index1][$entry]) {
                    $low_limit       = 10;
                    $low_warn_limit  = 15;
                    $high_warn_limit = 50;
                    $high_limit      = 60;

                    $slotnum    = $sensor_oids[$index1]['slotIndex'];
                    $name       = $sensor_oids[$index1]['slotCardUnitName'];
                    $descr      = $name." [Slot ".$slotnum."]";
                    $current    = $sensor_oids[$index1][$entry];
                    $sensorType = 'fsp150temp';
                    $oid        = $temp_sensors[$entry].".".$index1;

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
}// ************** End of Sensors for ADVA FSP150 GE11x **********


// *************************************************************
// ***** Sensors for ADVA FSP3000 R7
// *************************************************************

if ($device['sysObjectID'] == 'enterprises.2544.1.11.1.1') {
    $advafsp3kr7_oids = snmpwalk_cache_multi_oid($device, 'pmSnapshotCurrentEntry', $advafsp3kr7_oids, 'ADVA-FSPR7-MIB', '/opt/librenms/mibs/adva', '-OQUbs');
    $advafsp3kr7_oids = snmpwalk_cache_multi_oid($device, 'entityFacilityOneIndex', $advafsp3kr7_oids, 'ADVA-FSPR7-MIB', '/opt/librenms/mibs/adva', '-OQUbs');
    $advafsp3kr7_oids = snmpwalk_cache_multi_oid($device, 'entityDcnOneIndex', $advafsp3kr7_oids, 'ADVA-FSPR7-MIB', '/opt/librenms/mibs/adva', '-OQUbs');
    $advafsp3kr7_oids = snmpwalk_cache_multi_oid($device, 'entityOpticalMuxOneIndex', $advafsp3kr7_oids, 'ADVA-FSPR7-MIB', '/opt/librenms/mibs/adva', '-OQUbs');
    $advafsp3kr7_oids = snmpwalk_cache_multi_oid($device, 'entityFacilityAidString', $advafsp3kr7_oids, 'ADVA-FSPR7-MIB', '/opt/librenms/mibs/adva', '-OQUbs');
    $advafsp3kr7_oids = snmpwalk_cache_multi_oid($device, 'entityEqptAidString', $advafsp3kr7_oids, 'ADVA-FSPR7-MIB', '/opt/librenms/mibs/adva', '-OQUbs');
    $advafsp3kr7_oids = snmpwalk_cache_multi_oid($device, 'entityDcnAidString', $advafsp3kr7_oids, 'ADVA-FSPR7-MIB', '/opt/librenms/mibs/adva', '-OQUbs');
    $advafsp3kr7_oids = snmpwalk_cache_multi_oid($device, 'entityOpticalMuxAidString', $advafsp3kr7_oids, 'ADVA-FSPR7-MIB', '/opt/librenms/mibs/adva', '-OQUbs');
    $advafsp3kr7_oids = snmpwalk_cache_multi_oid($device, 'plugMaxDataRate', $advafsp3kr7_oids, 'ADVA-FSPR7-MIB', '/opt/librenms/mibs/adva', '-OQUbs');
    $advafsp3kr7_oids = snmpwalk_cache_multi_oid($device, 'plugAdmin', $advafsp3kr7_oids, 'ADVA-FSPR7-MIB', '/opt/librenms/mibs/adva', '-OQUbs');
    $advafsp3kr7_oids = snmpwalk_cache_multi_oid($device, 'physicalPortFrequency', $advafsp3kr7_oids, 'ADVA-FSPR7-MIB', '/opt/librenms/mibs/adva', '-OQUbs');
    $advafsp3kr7_oids = snmpwalk_cache_multi_oid($device, 'plugTransmitChannel', $advafsp3kr7_oids, 'ADVA-FSPR7-MIB', '/opt/librenms/mibs/adva', '-OQUbs');
    $advafsp3kr7_oids = snmpwalk_cache_multi_oid($device, 'plugFiberType', $advafsp3kr7_oids, 'ADVA-FSPR7-MIB', '/opt/librenms/mibs/adva', '-OQUbs');
    $advafsp3kr7_oids = snmpwalk_cache_multi_oid($device, 'plugReach', $advafsp3kr7_oids, 'ADVA-FSPR7-MIB', '/opt/librenms/mibs/adva', '-OQUbs');

    $fsp3kr7_Card = snmpwalk_cache_multi_oid($device, 'entityEqptAidString', $fsp3kr7_Card, 'ADVA-FSPR7-MIB', '/opt/librenms/mibs/adva', '-OQUbs');
    $fsp3kr7_Card = snmpwalk_cache_multi_oid($device, 'eqptPhysInstValueEntry', $fsp3kr7_Card, 'ADVA-FSPR7-PM-MIB', '/opt/librenms/mibs/adva', '-OQUbs');
    $fsp3kr7_Card = snmpwalk_cache_multi_oid($device, 'optMuxPhysInstValueTable', $fsp3kr7_Card, 'ADVA-FSPR7-PM-MIB', '/opt/librenms/mibs/adva', '-OQUbs');
    $fsp3kr7_Card = snmpwalk_cache_multi_oid($device, 'entityMtosiSlotsAidString', $fsp3kr7_Card, 'ADVA-FSPR7-PM-MIB', '/opt/librenms/mibs/adva', '-OQUbs');
    $fsp3kr7_Card = snmpwalk_cache_multi_oid($device, 'eqptPhysThresholdEntry', $fsp3kr7_Card, 'ADVA-FSPR7-PM-MIB', '/opt/librenms/mibs/adva', '-OQUbs');

//    $results = print_r($fsp3kr7_Card, true); // $results now contains output from print_r
//    file_put_contents('/opt/librenms/adva-precache.txt', $results);
//    var_dump($results);

    foreach (array_keys($fsp3kr7_Card) as $index => $entity) {
        foreach (array_keys($advafsp3kr7_oids) as $entity => $content) {
            $fsp3kr7_Card[$index] = implode('.', explode('.', array_keys($advafsp3kr7_oids[$entity]), -2));

            if ($advafsp3kr7_oids[$content]['entityFacilityAidString']) {
                $advafsp3kr7_oids[$content]['AidString'] = $advafsp3kr7_oids[$content]['entityFacilityAidString'];
                $advafsp3kr7_oids[$content]['OneIndex']  = $advafsp3kr7_oids[$content]['entityFacilityOneIndex'];
            }
            if ($advafsp3kr7_oids[$content]['entityDcnAidString']) {
                $advafsp3kr7_oids[$content]['AidString'] = $advafsp3kr7_oids[$content]['entityDcnAidString'];
                $advafsp3kr7_oids[$content]['OneIndex']  = $advafsp3kr7_oids[$content]['entityDcnOneIndex'];
            }
            if ($advafsp3kr7_oids[$content]['entityOpticalMuxAidString']) {
                $advafsp3kr7_oids[$content]['AidString'] = $advafsp3kr7_oids[$content]['entityOpticalMuxAidString'];
                $advafsp3kr7_oids[$content]['OneIndex']  = $advafsp3kr7_oids[$content]['entityOpticalMuxOneIndex'];
            }
        }
        //   $content[$entity] = str_replace($replace, "", $content[$entity][$replace]);
        //        $entity = implode('.', explode('.', $entity, -2);
    } //end test

    $multiplier = 1;
    $divisor    = 10;

    if (is_array($fsp3kr7_Card)) {
        foreach (array_keys($fsp3kr7_Card) as $index) {
            if ($fsp3kr7_Card[$index]['eqptPhysInstValueTemp']){ 
                $low_limit = 10;
                $low_warn_limit = 15;
                $high_warn_limit = 35;
                $high_limit = $fsp3kr7_Card[$index]['eqptPhysThresholdTempHigh']/$divisor;

                $slotnum    = $index;
                $descr      = strtoupper($fsp3kr7_Card[$index]['entityEqptAidString']);
                $current    = $fsp3kr7_Card[$index]['eqptPhysInstValueTemp'];
                $sensorType = 'fsp3kr7temp';
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
