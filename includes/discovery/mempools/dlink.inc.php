<?php
/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2018 Thomas GAGNIERE
 * @author     Thomas GAGNIERE <tgagniere@reseau-concept.com>
 */
if ($device['os'] == 'dlink') {
    echo 'D-Link : ';
    
    $memory_oid = '.1.3.6.1.4.1.171.12.1.1.9.1.4.1';
    $usage = snmp_get($device, $memory_oid, '-Ovq');
    if (is_numeric($usage)) {
        discover_mempool($valid_mempool, $device, '0', 'dlink', 'Memory', '1', null, null);
    }

    $dlink_mempools = snmpwalk_cache_oid($device, 'dEntityExtMemoryUtilTable', [], 'DLINKSW-ENTITY-EXT-MIB');
    foreach ($dlink_mempools as $tmp_index => $dlink_data) {
        list(,$dlink_type) = explode('.', $tmp_index);
        discover_mempool($valid_mempool, $device, $tmp_index, 'dlink', ucfirst($dlink_type). " Memory");
    }
}
