<?php
/*
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.


 * @package    LibreNMS
 * @subpackage FiberHome Switch Device Support - mempools module
 * @link       http://librenms.org
 * @copyright  2018 Christoph Zilian <czilian@hotmail.com>
 * @author     Christoph Zilian <czilian@hotmail.com>
*/

$oid = $mempool['mempool_index'];
    echo "\nFiberHome-MEMORY-POOL Index: ".$mempool['mempool_index']."\n";

if (!is_array($mempool_cache['fiberhome-switch'])) {
    echo "\ncaching\n";
    $mempool_cache['fiberhome-switch'] = snmpwalk_group($device, 'memoryPoolTable', 'WRI-MEMORY-MIB');
    d_echo($mempool_cache);
}

$entry = $mempool_cache['fiberhome-switch'][$mempool['mempool_index']];

if ($entry['memoryPoolTotalBytes'] < 0) {
    $entry['memoryPoolTotalBytes'] = ($entry['memoryPoolTotalBytes'] * -1);
}

$perc             = $entry['memoryPoolCurrUsage'];
$mempool['total'] = $entry['memoryPoolTotalBytes'];
$mempool['used']  = $entry['memoryPoolAllocBytesNum'];
$mempool['free']  = ($entry['memoryPoolTotalBytes'] - $mempool['used']);

// End of File
