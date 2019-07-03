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

$memory_pool = snmp_get_multi_oid($device, ['bdcomMemoryPoolUsed.0', 'bdcomMemoryPoolFree.0'], '-OQUs', 'BDCOM-MEMORY-POOL-MIB');

$mempool['free']  = ['bdcomMemoryPoolFree.0'];
$mempool['used']  = $memory_pool['bdcomMemoryPoolUsed.0'];
$mempool['total'] = $mempool['free'] + $mempool['used'];
$mempool['perc']  = $mempool['used'] / $mempool['total'];
