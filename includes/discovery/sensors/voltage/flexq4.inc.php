<?php
/**
 * flexq4.inc.php
 *
 * LibreNMS voltage discovery module for FlexScada Q4 as Used by Celerity Networks LLC
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
 * @copyright  2017 Neil Lathwood
 * @author     Neil Lathwood <gh+n@laf.io>
 */

$oid = '.1.3.6.1.4.1.4128.1';
$current = (snmp_get($device, $oid, '-Oqv'));
discover_sensor($valid['sensor'], 'voltage', $device, $oid, 1, 'flexq4', 'input1-Solar', 1, 1, null, null, null, null, $current);

$oid = '.1.3.6.1.4.1.4128.3';
$current = (snmp_get($device, $oid, '-Oqv'));
discover_sensor($valid['sensor'], 'voltage', $device, $oid, 2, 'flexq4', 'input3-Batt', 1, 1, null, null, null, null, $current);

$oid = '.1.3.6.1.4.1.4128.5';
$current = (snmp_get($device, $oid, '-Oqv'));
discover_sensor($valid['sensor'], 'voltage', $device, $oid, 3, 'flexq4', 'input5-24v', 1, 1, null, null, null, null, $current);

$oid = '.1.3.6.1.4.1.4128.7';
$current = (snmp_get($device, $oid, '-Oqv'));
discover_sensor($valid['sensor'], 'voltage', $device, $oid, 4, 'flexq4', 'input7-48v', 1, 1, null, null, null, null, $current);

$oid = '.1.3.6.1.4.1.4128.8';
$current = (snmp_get($device, $oid, '-Oqv'));
discover_sensor($valid['sensor'], 'voltage', $device, $oid, 5, 'flexq4', 'input8-GenRun', 1, 1, null, null, null, null, $current);
