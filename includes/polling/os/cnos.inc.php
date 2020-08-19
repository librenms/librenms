<?php
/*
 * Lenovo CNOS information module
 *
 * Copyright (c) 2020 Florian Zillner <zillo83@gmail.com>
 * Parts Copyright (c) 2015 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
$sysdescr_value = $device['sysDescr'];
if (strpos($sysdescr_value, 'Lenovo ThinkSystem') !== false) {
    $hardware = str_replace('Lenovo ThinkSystem', '', $sysdescr_value);
    $version = trim(snmp_get($device, '.1.3.6.1.2.1.47.1.1.1.1.10.1', '-Ovq'), '" ');
    $serial = trim(snmp_get($device, '.1.3.6.1.2.1.47.1.1.1.1.11.1', '-Ovq'), '" ');
}//end if
