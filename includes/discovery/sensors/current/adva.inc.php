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
    $egxPSU       = snmpwalk_cache_multi_oid($device, 'psuTable', $egxPSU, 'CM-ENTITY-MIB', '/opt/librenms/mibs/adva', '-OQUbs');

    $multiplier = 1;
    $divisor    = 1000;

    if (is_array($egxPSU)) {
        foreach (array_keys($egxPSU) as $index) {

            $low_limit = 1;
            $low_warn_limit = 2;
            $high_warn_limit = 25;
            $high_limit = 30;

            $slotnum    = substr($index, 4);
            $psuname    = "PSU[".strtoupper($egxPSU[$index]['psuType'])."]";
            $descr      = $psuname." #".$slotnum.' DC Output';
            $current    = $egxPSU[$index]['psuOutputCurrent'];
            $sensorType = 'fsp150egxOutputCurrent';
            $oid        = '.1.3.6.1.4.1.2544.1.12.3.1.4.1.8.'.$index;

            discover_sensor($valid['sensor'],
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
		$current);
        }
    }
}// *****  End If of FSP150EG-X

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
            if ($fsp3kr7_Card[$index]['eqptPhysInstValuePsuAmpere']) {
               if ($fsp3kr7_Card[$index]['eqptPhysInstValuePsuAmpere'] > 1000) {
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
            $current    = $fsp3kr7_Card[$index]['eqptPhysInstValuePsuAmpere'];
            $sensorType = 'fsp3kr7psuInputA';
            $oid        = '.1.3.6.1.4.1.2544.1.11.11.1.2.1.1.1.6.'.$index;

            discover_sensor($valid['sensor'], 'current', $device, $oid, $index, $sensorType, $descr,
                            $divisor, $multiplier, $low_limit, $low_warn_limit, $high_warn_limit, $high_limit, $current);
         }
       }
    }
}// ******** End If of FSP3000 R7
