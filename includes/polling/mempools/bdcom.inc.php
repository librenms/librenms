<?php
/*
 *
 * LibreNMS mempools polling module for BDCom switches
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.

 * @package    LibreNMS
 * @subpackage polling
 * @link       http://librenms.org
 * @copyright  2017 Carlos A. Pedreros Lizama
 * @author     Carlos A. Pedreros Lizama <carlos.pedreros@gmail.com>
 */

echo 'BDCOM Memory Pool';

$memory_pool = snmp_get_multi_oid($device, 'nmsMemoryPoolTotalMemorySize.0 nmsMemoryPoolUtilization.0', '-OQUs', 'NMS-MEMORY-POOL-MIB');

$mempool['total'] = $memory_pool['nmsMemoryPoolTotalMemorySize.0'];
$mempool['perc']  = $memory_pool['nmsMemoryPoolUtilization.0'];
$mempool['used']  = ($mempool['total'] / 100 * $mempool['perc']);
$mempool['free']  = ($mempool['total'] - $mempool['used']);
