<?php
/*
 *
 * LibreNMS mempools discovery module for NETGEAR switches
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.

 * @package    LibreNMS
 * @subpackage discovery
 * @link       http://librenms.org
 */

if ($device['os'] == 'netgear') {
    echo 'NETGEAR: ';

    $memory_pool = snmp_get_multi_oid($device, ['agentSwitchCpuProcessMemAvailable.0', 'agentSwitchCpuProcessMemFree.0'], '-OQUs', 'NETGEAR-SWITCHING-MIB');

    if (is_numeric($memory_pool['agentSwitchCpuProcessMemAvailable.0']) && is_numeric($memory_pool['agentSwitchCpuProcessMemFree.0'])) {
        discover_mempool($valid_mempool, $device, 0, 'netgear', 'Memory', '1', null, null);
    }
}
