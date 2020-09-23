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

$temp_data = snmp_get_multi_oid($device, ['sysMemorySize.1', 'sysMemoryBusy.1'], '-OUQs', 'NAG-MIB');
$mempool['total'] = $temp_data['sysMemorySize.1'];
$mempool['free'] = $temp_data['sysMemoryBusy.1'];
$mempool['used'] = $mempool['total'] - $mempool['free'];
unset($temp_data);
