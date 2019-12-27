<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2019 hartred <tumanov@asarta.ru>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
 
$hardware = trim(snmp_get($device, '.1.3.6.1.4.1.10072.2.20.1.1.1.1.1.9.1', '-OQv'), '"');
$version = trim(snmp_get($device, '.1.3.6.1.4.1.10072.2.20.1.1.1.1.1.7.1', '-OQv'), '"');
$serial = trim(snmp_get($device, '.1.3.6.1.4.1.10072.2.20.1.1.2.1.1.18.1.1', '-OQv'), '"');

if (empty($hardware) && empty($version)) {
    $hardware =  $hardware;
    $version = $software;
    $serial = $serial;
    unset($temp_data);
}
