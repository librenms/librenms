<?php
/*
 * LibreNMS Quanta LB6M Temperature information module
 *
 * Copyright (c) 2017 Mark Guzman <segfault@hasno.info>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

d_echo('Quanta Temperatures');
$sensor_type = 'quanta_temp';
//FASTPATH-BOXSERVICES-PRIVATE-MIB::boxServicesTempSensorIndex
$sensors_id_oid = '.1.3.6.1.4.1.4413.1.1.43.1.8.1.4';
$sensors_values = snmp_walk($device, $sensors_id_oid, '-Ovq');
$sensor_id = 0;

foreach (explode("\n", $sensors_values) as $current_value) {
    $descr = 'Temperature '.$sensor_id.':';
    //FASTPATH-BOXSERVICES-PRIVATE-MIB::boxServicesTempSensorTemperature
    $sensor_id_oid = '.1.3.6.1.4.1.4413.1.1.43.1.8.1.4.'.$sensor_id;

    if ($current_value > 0) {
        discover_sensor($valid['sensor'], 'temperature', $device, $sensor_id_oid, $sensor_id, $sensor_type, $descr, 1, 1, null, null, null, null, $current_value);
    }

    $sensor_id++;
}
