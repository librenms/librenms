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

$temp_data = snmp_get_multi_oid($device, ['memTotalSize.0', 'memFreeSize.0'], '-OUQs', 'FD-SYSTEM-MIB:CDATA-EPON-MIB:NSCRTV-FTTX-EPON-MIB:NSCRTV-PON-TREE-EXT-MIB');
$mempool['total'] = $temp_data['memTotalSize.0'];
$mempool['free'] = $temp_data['memFreeSize.0'];
$mempool['used'] = $mempool['total'] - $mempool['free'];
unset($temp_data);
