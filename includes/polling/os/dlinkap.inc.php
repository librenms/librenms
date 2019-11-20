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

$firmware_oid = $device['sysObjectID'].'.5.1.1.0';
$hardware_oid = $device['sysObjectID'].'.5.1.5.0';

$version  = snmp_get($device, $firmware_oid, '-Oqv');
$hardware = $device['sysDescr'].' '.str_replace('"', '', snmp_get($device, $hardware_oid, '-Oqv'));
