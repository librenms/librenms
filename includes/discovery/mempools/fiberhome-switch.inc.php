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

if ($device['os'] === 'fiberhome-switch') {
    echo "\nFiberHome-MEMORY-POOL:\n";
    $mempools_array = snmpwalk_group($device, 'memoryPoolTable', 'WRI-MEMORY-MIB');
    d_echo($mempools_array);

    if (is_array($mempools_array)) {
        foreach ($mempools_array as $index => $entry) {
            if ($entry['memoryPoolTotalBytes'] != 0) {
                d_echo($index.' '.$entry['memoryPoolIndexDescr'].' -> '.$entry['memoryPoolAllocBytesNum']."\n");
                $usage_oid = '.1.3.6.1.4.1.3807.1.8012.1.5.4.1.7.'.$index;
                $descr     = $entry['memoryPoolIndexDescr'];
                $usage     = $entry['memoryPoolAllocBytesNum'];
                if (!strstr($descr, 'No') && !strstr($usage, 'No') && $descr != '') {
                    discover_mempool($valid_mempool, $device, $index, 'fiberhome-switch', $descr, '1', null, null);
                }
            } //end if
        } //end foreach
    } //end if
} // End of File
