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

echo 'Dlink AP CPU Usage';

$processor_oid=$device['sysObjectID'].'.5.1.3.0';
$usage = snmp_get($device, $processor_oid, '-OvQ', '');

if (is_numeric($usage)) {
    $proc = $usage;
}
