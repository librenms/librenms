<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2016 Søren Friis Rosiak <sorenrosiak@gmail.com> 
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
 
$data = explode(" ", $device['sysDescr']);
$hardware = $data[0];
$version = $data[9];

if (str_contains($device['sysDescr'], 'PLANET IGS-')) {
    $hardware = $data[1];
    $version = snmp_get($device, "1.3.6.1.2.1.47.1.1.1.1.10.1", "-Ovq");
}
