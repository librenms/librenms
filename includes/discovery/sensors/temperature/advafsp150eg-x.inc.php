<?php
/**
 * LibreNMS - ADVA FSP150-EGX (MetroE Core Switch) device support
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

if ($device['os'] == 'advafsp150eg-x') {
    $egx_oids = array();
    $egxPSU   = array();
    $egxSWF   = array();

    echo 'Caching OIDs:'."\n";

    $egxPSU   = snmpwalk_cache_multi_oid($device, 'psuTable', $egxPSU, 'CM-ENTITY-MIB', '/opt/librenms/mibs/advafsp150eg-x', '-OQUbs');
    $egxSWF   = snmpwalk_cache_multi_oid($device, 'ethernetSWFCardTable', $egxSWF, 'CM-ENTITY-MIB', '/opt/librenms/mibs/advafsp150eg-x', '-OQUbs');
    $egx_oids = snmpwalk_cache_multi_oid($device, 'ethernetSWFCardTable', $egx_oids, 'CM-ENTITY-MIB', '/opt/librenms/mibs/advafsp150eg-x', '-OQUbs');
    $egx_oids = snmpwalk_cache_multi_oid($device, 'ethernet1x10GHighPerCardTable', $egx_oids, 'CM-ENTITY-MIB', '/opt/librenms/mibs/advafsp150eg-x', '-OQUbs');
    $egx_oids = snmpwalk_cache_multi_oid($device, 'ethernet10x1GHighPerCardTable', $egx_oids, 'CM-ENTITY-MIB', '/opt/librenms/mibs/advafsp150eg-x', '-OQUbs');



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
            $psuname    = "PSU[".strtoupper($egxPSU[$index]['psuType'])."]";
            $descr      = $psuname." #".$slotnum;
            $current    = $egxPSU[$index]['psuTemperature'];
            $sensorType = 'advafsp150eg-x';
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
    }
   
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
            $swfname    = "SWF[".$egxSWF[$index]['ethernetSWFCardOperationalState']."]";
            $descr      = $swfname." #".$slotnum;
            $current    = $egxSWF[$index]['ethernetSWFCardTemperature'];
            $sensorType = 'advafsp150eg-x';
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
    }
} //end if
