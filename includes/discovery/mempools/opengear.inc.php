<?php
/*
 *
 * LibreNMS mempools discovery module for opengear devices
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

if ($device['os'] == 'opengear') {
    $usage = snmp_get($device, 'memIndex.0', '-OvQ', 'UCD-SNMP-MIB');
    if (is_numeric($usage)) {
        discover_mempool($valid_mempool, $device, '0', 'opengear', 'Memory', '1', null, null);
    }
}
