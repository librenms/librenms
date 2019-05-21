<?php
/*
 *
 * LibreNMS mempools discovery module for BDCom switches
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.

 * @package    LibreNMS
 * @subpackage discovery
 * @link       http://librenms.org
 * @copyright  2017 Carlos A. Pedreros Lizama
 * @author     Carlos A. Pedreros Lizama <carlos.pedreros@gmail.com>
 */

if ($device['os'] == 'bdcom') {
    echo 'BDCOM: ';

    $memory_pool = snmp_get_multi_oid($device, ['bdcomMemoryPoolUsed.0', 'bdcomMemoryPoolFree.0'], '-OQUs', 'BDCOM-MEMORY-POOL-MIB');

    if (is_numeric($memory_pool['bdcomMemoryPoolUsed.0']) && is_numeric($memory_pool['bdcomMemoryPoolFree.0'])) {
        discover_mempool($valid_mempool, $device, 0, 'bdcom', 'Memory', '1', null, null);
    }
}
