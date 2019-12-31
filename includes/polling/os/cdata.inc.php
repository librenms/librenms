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
 
$gwd_temp = snmp_get_multi_oid($device, ['.1.3.6.1.4.1.34592.1.3.1.5.2.1.1.4.0', '.1.3.6.1.4.1.34592.1.3.1.5.2.1.1.5.0', '.1.3.6.1.4.1.17409.2.3.1.3.1.1.9.1.0', '.1.3.6.1.4.1.17409.2.3.1.3.1.1.7.1.0', '.1.3.6.1.4.1.17409.2.3.1.1.13.0'], '-OQUn');
$version   = $gwd_temp['.1.3.6.1.4.1.17409.2.3.1.3.1.1.9.1.0'];
if (empty($version)) {
    $version   = $gwd_temp['.1.3.6.1.4.1.34592.1.3.1.5.2.1.1.5.0'];
}
$hardware  = $gwd_temp['.1.3.6.1.4.1.17409.2.3.1.3.1.1.7.1.0'];
if (empty($hardware)) {
    $hardware  = $gwd_temp['.1.3.6.1.4.1.34592.1.3.1.5.2.1.1.4.0'];
}
$serial    = $gwd_temp['.1.3.6.1.4.1.17409.2.3.1.1.13.0'];
