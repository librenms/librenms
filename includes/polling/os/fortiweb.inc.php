<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2018 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$temp_data  = snmp_get_multi_oid($device, ['fnSysSerial.0', 'fwSysModel.0', 'fwSysVersion.0'], '-OUQs', 'FORTINET-FORTIWEB-MIB');
$temp_version = explode(' ', $temp_data['fwSysVersion.0']);

$hardware        = $temp_data['fwSysModel.0'];
$serial          = $temp_data['fnSysSerial.0'];
$version         = $temp_version[1];

unset($temp_data, $temp_version);
