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
 * @copyright  2017 Thomas GAGNIERE
 * @author     Thomas GAGNIERE <tgagniere@reseau-concept.com>
 */

if ($device['os'] == 'dlinkap') {
    d_echo('Dlink AP');
    $memory_oid = $device['sysObjectID'].'.5.1.4.0';
    $usage = snmp_get($device, $memory_oid, '-Ovq');
    if (is_numeric($usage)) {
        discover_mempool($valid_mempool, $device, '0', 'dlinkap', 'Memory', '1', null, null);
    }
}
