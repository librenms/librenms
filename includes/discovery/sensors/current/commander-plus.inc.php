<?php
/**
 * commander-plus.inc.php
 *
 * LibreNMS current discovery module for Commander Plus
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
$current = snmp_get($device, 'batteryCurrent.0', '-Oqv', 'CCPOWER-MIB');
$oid = '.1.3.6.1.4.1.18642.1.2.2.1.0';
$descr = 'Battery current';
$divisor = 1;
$multiplier = 1;
discover_sensor($valid['sensor'], 'current', $device, $oid, 'batteryCurrent', 'commander-plus', $descr, $divisor, $multiplier, null, null, null, null, $current);

$current = snmp_get($device, 'rectifierLoadCurrent.0', '-Oqv', 'CCPOWER-MIB');
$oid = '.1.3.6.1.4.1.18642.1.2.1.2.0';
$descr = 'Rectifier Current';
$divisor = 1;
$multiplier = 1;
$limit_low = 0;
$limit = 5000;
discover_sensor($valid['sensor'], 'current', $device, $oid, 'rectifierLoadCurrent', 'commander-plus', $descr, $divisor, $multiplier, $limit_low, null, null, $limit, $current);
