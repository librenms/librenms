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

    // Define Sensors and Limits
    $sensors_adva = [
        [
        'sensor_name'     => 'psuOutputCurrent',
        'sensor_oid'      => '.1.3.6.1.4.1.2544.1.12.3.1.4.1.8'
        ]
    ];

    $multiplier = 1;
    $divisor    = 1000;
    
    foreach (array_keys($pre_cache['adva_fsp150']) as $index) {
        foreach ($sensors_adva as $entry) {
            $sensor_name = $entry['sensor_name'];
            if ($pre_cache['adva_fsp150'][$index][$sensor_name]) {
                $oid          = $entry['sensor_oid'].".".$index;
                $descr        = $pre_cache['adva_fsp150'][$index]['slotCardUnitName']." [#".$pre_cache['adva_fsp150'][$index]['slotIndex']."]";
                $current      = $pre_cache['adva_fsp150'][$index][$entry['sensor_name']]/$divisor;

                discover_sensor(
                    $valid['sensor'],
                    'current',
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
