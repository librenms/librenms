<?php
/*
 * LibreNMS Ubiquiti EdgeSwitch Fan information module
 *
 * Copyright (c) 2018 Jean-Louis Dupond <jea-louis@dupond.be>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
d_echo('UBNT EdgeSwitch Fans');
$sensor_type = 'edgeswitch_fan';
//EdgeSwitch-BOXSERVICES-PRIVATE-MIB::boxServicesFansIndex
$sensors_id_oid = '.1.3.6.1.4.1.4413.1.1.43.1.6.1.1';
$sensors_id = snmp_walk($device, $sensors_id_oid, '-Ovq');
foreach (explode("\n", $sensors_id) as $sensor) {
    $descr = 'Fan '.$sensor.':';
    //EdgeSwitch-BOXSERVICES-PRIVATE-MIB::boxServicesFanSpeed
    $sensor_id_oid = '.1.3.6.1.4.1.4413.1.1.43.1.6.1.4.1.0.'.$sensor;
    $current_value = trim(snmp_get($device, $sensor_id_oid, '-Oqv'));
    discover_sensor($valid['sensor'], 'fanspeed', $device, $sensor_id_oid, $sensor, $sensor_type, $descr, 1, 1, null, null, null, null, $current_value);
}
