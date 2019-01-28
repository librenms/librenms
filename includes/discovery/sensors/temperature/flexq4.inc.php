<?php
/**
 * flexq4.inc.php
 *
 * LibreNMS temperature discovery module for FlexScada Q4 as Used by Celerity Networks LLC
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

$oid = '.1.3.6.1.4.1.4128.49';
$current = (snmp_get($device, $oid, '-Oqv'));
discover_sensor($valid['sensor'], 'temperature', $device, $oid, 1, 'flexq4', 'temp1', 1, 1, null, null, null, null, $current);

$oid = '.1.3.6.1.4.1.4128.50';
$current = (snmp_get($device, $oid, '-Oqv'));
discover_sensor($valid['sensor'], 'temperature', $device, $oid, 2, 'flexq4', 'temp2', 1, 1, null, null, null, null, $current);
