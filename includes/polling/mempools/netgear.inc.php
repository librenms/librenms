<?php
/*
 *
 * LibreNMS mempools polling module for NETGEAR switches
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.

 * @package    LibreNMS
 * @subpackage polling
 * @link       http://librenms.org
 */

echo 'NETGEAR Memory Pool';

$memory_pool = snmp_get_multi_oid($device, ['agentSwitchCpuProcessMemAvailable.0', 'agentSwitchCpuProcessMemFree.0'], '-OQUs', 'NETGEAR-SWITCHING-MIB');

$mempool['free']  = $memory_pool['agentSwitchCpuProcessMemFree.0'];
$mempool['used']  = $memory_pool['agentSwitchCpuProcessMemAvailable.0'] - $mempool['free'];
$mempool['total'] = $mempool['free'] + $mempool['used'];
$mempool['perc']  = $mempool['used'] / $mempool['total'];
