<?php
/*
 * LibreNMS - ADVA FSP150-CC-GE11x (MetroE Edge) device support
 *
 * Copyright (c) 2017 Christoph Zilian <czilian@hotmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if ($device['os'] == 'advafsp150ge11x') {

    $ge11x_oids = array();
    echo 'Caching OIDs:'."\n";
    $ge11x_oids   = snmpwalk_cache_multi_oid($device, 'cmEntityObjects' , $ge11x_oids,   'CM-ENTITY-MIB', '/opt/librenms/mibs/advafsp150ge11x', '-OQUbs');


    $multiplier = 1;
    $divisor    = 1;

    $ge11x_temp_sensors = array('ethernetNTEGE114CardTemperature'  => '.1.3.6.1.4.1.2544.1.12.3.1.26.1.6' ,
                                'ethernetNTEGE114SCardTemperature' => '.1.3.6.1.4.1.2544.1.12.3.1.46.1.6'); 

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

                  echo "---------------Temperature Sensors--------------\n";
                  echo "descr   : ".$descr."\n";
                  echo "oid     : ".$oid."\n";
                  echo "current : ".$current."\n";

                  discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index1, $sensorType, $descr,
                                  $divisor, $multiplier, $low_limit, $low_warn_limit, $high_warn_limit, $high_limit, $current);

              }//End if sensor exists
           }//End foreach $entry
        }//End foreach $index
    } //End if  oids exist
} //End if OS check
