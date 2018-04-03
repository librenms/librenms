<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2015 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */
/**
 * hiveos-wireless.inc.php
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2018 Ryan Finney
 * @author     https://github.com/theherodied/
 */

if (preg_match('/^(.+?),/', $device['sysDescr'], $hardware)) {
    $hardware = $store[1];
}
$hardware = trim(snmp_get($device, '.1.3.6.1.4.1.4413.1.1.1.1.1.3.0', '-Ovq'), '"');
$version = trim(snmp_get($device, '.1.3.6.1.4.1.4413.1.1.1.1.1.13.0', '-Ovq'), '"');
$serial = trim(snmp_get($device, '.1.3.6.1.4.1.4413.1.1.1.1.1.4.0', '-Ovq'), '"');

$ap250 = trim(snmp_get($device, '.1.3.6.1.4.1.26928.1.2.6.0', '-OQv', '', ''), '" ');
if ($ap250 == "AP250") {
    // SNMPv2-SMI::enterprises.6530.11.1.0 = STRING: "2N IP Force"
    $hardware = trim(snmp_get($device, '.1.3.6.1.4.1.26928.1.2.6.0', '-OQv', '', ''), '" ');

    // SNMPv2-SMI::enterprises.6530.11.4.0 = STRING: "2.22.0.31.8"
    $version = trim(snmp_get($device, '.1.3.6.1.4.1.26928.1.2.12.0', '-OQv', '', ''), '" ');

    // SNMPv2-SMI::enterprises.6530.11.3.0 = STRING: "54-0880-2424"
    $serial = trim(snmp_get($device, '.1.3.6.1.4.1.26928.1.2.5.0', '-OQv', '', ''), '" ');
}
