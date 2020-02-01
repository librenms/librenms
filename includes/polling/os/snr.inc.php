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
 
$hardware = trim(snmp_get($device, '.1.3.6.1.4.1.40418.7.100.1.2.0', '-OQv'), '"');
$version = trim(snmp_get($device, '.1.3.6.1.4.1.40418.7.100.1.3.0', '-OQv'), '"');

if (empty($hardware) && empty($version)) {
    $temp_data = snmp_get_multi_oid($device, ['sysHardwareVersion.1', 'sysSoftwareVersion.1'], '-OUQs', 'NAG-MIB');
    $hardware =  $temp_data['sysHardwareVersion.1'];
    $version = $temp_data['sysSoftwareVersion.1'];
    unset($temp_data);
}
