<?php
/**
 * LibreNMS - FiberHome Switch device support - mempools module
 *
 * @category   Network_Monitoring
 * @package    LibreNMS
 * @subpackage Fiber Home Switch device support
 * @author     Christoph Zilian <czilian@hotmail.com>
 * @license    http://gnu.org/copyleft/gpl.html GNU GPL
 * @link       https://github.com/librenms/librenms/

 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 **/

$oid = $mempool['mempool_index'];
    echo "\nFiberHome-MEMORY-POOL Index: ".$mempool['mempool_index']."\n";

if (!is_array($mempool_cache['fiberhome-switch'])) {
    echo "\ncaching\n";
    $mempool_cache['fiberhome-switch'] = array();
    $mempool_cache['fiberhome-switch'] = snmpwalk_cache_multi_oid($device, 'memoryPoolTotalBytes', $mempool_cache['fiberhome-switch'], 'WRI-MEMORY-MIB', 'fiberhome-switch');
    $mempool_cache['fiberhome-switch'] = snmpwalk_cache_multi_oid($device, 'memoryPoolAllocBytesNum', $mempool_cache['fiberhome-switch'], 'WRI-MEMORY-MIB', 'fiberhome-switch');
    $mempool_cache['fiberhome-switch'] = snmpwalk_cache_multi_oid($device, 'memoryPoolCurrUsage', $mempool_cache['fiberhome-switch'], 'WRI-MEMORY-MIB', 'fiberhome-switch');
    d_echo($mempool_cache);
}

$entry = $mempool_cache['fiberhome-switch'][$mempool[mempool_index]];

if ($entry['memoryPoolTotalBytes'] < 0) {
    $entry['memoryPoolTotalBytes'] = ($entry['memoryPoolTotalBytes'] * -1);
}

$perc             = $entry['memoryPoolCurrUsage'];
$mempool['total'] = $entry['memoryPoolTotalBytes'];
$mempool['used']  = $entry['memoryPoolAllocBytesNum'];
$mempool['free']  = ($entry['memoryPoolTotalBytes'] - $mempool['used']);

// End of File
