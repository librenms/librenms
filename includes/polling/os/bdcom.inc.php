<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2020 hartred <tumanov@asarta.ru>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
 
$hardware = trim(snmp_get($device, '.1.3.6.1.4.1.3320.3.6.10.1.5.0', '-OQv'), '"');
$version = trim(snmp_get($device, '.1.3.6.1.4.1.3320.3.6.10.1.6.0', '-OQv'), '"');
$serial = trim(snmp_get($device, '.1.3.6.1.4.1.3320.3.6.10.1.4.0', '-OQv'), '"');

if (empty($hardware) && empty($version)) {
    $temp_data = snmp_get_multi_oid($device, ['.1.3.6.1.4.1.3320.3.6.10.1.5.0', '.1.3.6.1.4.1.3320.3.6.10.1.6.0',  '.1.3.6.1.4.1.3320.3.6.10.1.4'], '-OQv', 'NMS-CHASSIS');
    $hardware =  $temp_data['.1.3.6.1.4.1.3320.3.6.10.1.5.0'];
    $version = $temp_data['.1.3.6.1.4.1.3320.3.6.10.1.6.0'];
	$serial = $temp_data['.1.3.6.1.4.1.3320.3.6.10.1.4.0'];
    unset($temp_data);
}
