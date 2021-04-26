<?php
/**
 * sitemonitor.inc.php
 *
 * LibreNMS temperature discovery module for Packetflux SiteMonitor
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2017 Neil Lathwood
 * @author     Neil Lathwood <gh+n@laf.io>
 */
$oid = '.1.3.6.1.4.1.32050.2.1.27.5.0';
$current = (snmp_get($device, $oid, '-Oqv') / 10);
discover_sensor($valid['sensor'], 'temperature', $device, $oid, 0, 'sitemonitor', 'Temperature', 10, 1, null, null, null, null, $current);

$oid = '.1.3.6.1.4.1.32050.2.1.27.5.5';
$current = (snmp_get($device, $oid, '-Oqv') / 10);
discover_sensor($valid['sensor'], 'temperature', $device, $oid, 5, 'sitemonitor', 'Relay on Above', 10, 1, null, null, null, null, $current);

$oid = '.1.3.6.1.4.1.32050.2.1.27.5.6';
$current = (snmp_get($device, $oid, '-Oqv') / 10);
discover_sensor($valid['sensor'], 'temperature', $device, $oid, 6, 'sitemonitor', 'Relay on Below', 10, 1, null, null, null, null, $current);
