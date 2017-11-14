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

if ($device['os'] === 'dlinkap') {
    echo 'Dlink AP : ';

    $processor_oid = $device['sysObjectID'].'.5.1.3.0';
    $descr = 'Processor';
    $usage = snmp_get($device, $processor_oid, '-OvQ');

    if (is_numeric($usage)) {
        discover_processor($valid['processor'], $device, $processor_oid, '0', 'dlinkap-cpu', $descr, '100', $usage, null, null);
    }
}
