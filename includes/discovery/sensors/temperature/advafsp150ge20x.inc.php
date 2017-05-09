<?php
/**
 * LibreNMS - ADVA FSP150-XG210 (MetroE Subaggregation) device support
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

if ($device['os'] == 'advafsp150ge20x') {
    echo 'Caching OIDs:'."\n";
    $ge20x_oids = array();
    $ge20x_oids = snmpwalk_cache_multi_oid($device, 'cmEntityObjects', $ge20x_oids, 'CM-ENTITY-MIB', '/opt/librenms/mibs/advafsp150ge20x', '-OQUbs');


    $multiplier = 1;
    $divisor    = 1;

    $ge20x_temp_sensors = array('ethernetNTEXG210CardTemperature'  => '.1.3.6.1.4.1.2544.1.12.3.1.30.1.6' ,
                                'ethernetXG1XCCCardTemperature'    => '.1.3.6.1.4.1.2544.1.12.3.1.31.1.6');
    if (is_array($ge20x_oids)) {
        echo "Temperature Sensors:\n";

        foreach (array_keys($ge20x_oids) as $index1) {
            foreach (array_keys($ge20x_temp_sensors) as $index2 => $entry) {
                if ($ge20x_oids[$index1][$entry]) {
                    $low_limit       = 10;
                    $low_warn_limit  = 15;
                    $high_warn_limit = 50;
                    $high_limit      = 60;

                    $slotnum    = $ge20x_oids[$index1]['slotIndex'];
                    $name       = $ge20x_oids[$index1]['slotCardUnitName'];
                    $descr      = $name." [Slot ".$slotnum."]";
                    $current    = $ge20x_oids[$index1][$entry];
                    $sensorType = 'advafsp150ge20x';
                    $oid        = $ge20x_temp_sensors[$entry].".".$index1;

                    echo "---------------Temperature Sensors--------------\n";
                    echo "descr   : ".$descr."\n";
                    echo "oid     : ".$oid."\n";
                    echo "current : ".$current."\n";

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
} //End if OS check
