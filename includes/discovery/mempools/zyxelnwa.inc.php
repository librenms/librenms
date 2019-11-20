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

if ($device['os'] == 'zyxelnwa') {
    d_echo('Zyxel NWA');
    $usage = snmp_get($device, '.1.3.6.1.4.1.890.1.6.22.1.2.0', '-Ovq');
    if (is_numeric($usage)) {
        discover_mempool($valid_mempool, $device, '0', 'zywall', 'Memory', '1', null, null);
    }
}
