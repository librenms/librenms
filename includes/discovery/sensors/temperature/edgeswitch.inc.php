<?php
/*
 * LibreNMS Ubiquiti EdgeSwitch Temperature information module
 *
 * Copyright (c) 2016 Cercel Valentin <crc@nuamchefazi.ro>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

d_echo('UBNT EdgeSwitch Temperatures');
$sensor_type = 'edgeswitch_temp';
//EdgeSwitch-BOXSERVICES-PRIVATE-MIB::boxServicesTempSensorIndex
$sensors_id_oid = '.1.3.6.1.4.1.4413.1.1.43.1.8.1.2';
$sensors_id = snmp_walk($device, $sensors_id_oid, '-Ovq');

foreach (explode("\n", $sensors_id) as $sensor) {
    $descr = 'Temperature '.$sensor.':';
    //EdgeSwitch-BOXSERVICES-PRIVATE-MIB::boxServicesTempSensorTemperature
    $sensor_id_oid = '.1.3.6.1.4.1.4413.1.1.43.1.8.1.5.1.'.$sensor;
    $current_value = trim(snmp_get($device, $sensor_id_oid, '-Oqv'));

    if ($current_value > 0) {
        discover_sensor($valid['sensor'], 'temperature', $device, $sensor_id_oid, $sensor, $sensor_type, $descr, 1, 1, null, null, null, null, $current_value);
    }
}
