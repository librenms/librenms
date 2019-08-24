<?php

/*
 * LibreNMS OS polling module for the Schleifenbauer SPDM databus ring
 *
 * Copyright (c) 2019 Martijn Schmidt <martijn.schmidt@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$master_unit     = snmp_get($device, "sdbMgmtCtrlDevUnitAddress.1", '-Oqv', 'SCHLEIFENBAUER-DATABUS-MIB');
$multi_get_array = snmp_get_multi($device, ["sdbDevIdProductId.$master_unit", "sdbDevIdFirmwareVersion.$master_unit", "sdbDevIdBuildNumber.$master_unit", "sdbDevIdSerialNumber.$master_unit"], '-OQUs', 'SCHLEIFENBAUER-DATABUS-MIB');

$hardware        = $multi_get_array[$master_unit]['sdbDevIdProductId'];
$serial          = $multi_get_array[$master_unit]['sdbDevIdSerialNumber'];
$version         = "- version ".$multi_get_array[$master_unit]['sdbDevIdFirmwareVersion'].", build ".$multi_get_array[$master_unit]['sdbDevIdBuildNumber'];
