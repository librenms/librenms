<?php
/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$version = $device['sysDescr'];
$cnpilot_data = snmp_get_multi_oid($device, ['cambiumAPSerialNum.0', 'cambiumAPHWType.0'], '-OUQs', 'CAMBIUM-MIB');

$hardware = $cnpilot_data['cambiumAPHWType.0'];
$serial = $cnpilot_data['cambiumAPSerialNum.0'];
