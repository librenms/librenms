<?php
/**
 * extendair.inc.php
 *
 * LibreNMS temperatures discover module for Exalt ExtendAir
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
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */
$oid = '.1.3.6.1.4.1.25651.1.2.4.2.4.1.3.0';
$index = 0;
$descr = 'Internal temp (far end radio)';
$value = snmp_get($device, 'remCurrentTemp.0', '-Oqv', 'ExaltComProducts');
if ($value) {
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'extendair', $descr, '1', '1', null, null, null, null, $value);
}

$oid = '.1.3.6.1.4.1.25651.1.2.4.2.3.1.3.0';
$index = 1;
$descr = 'Internal temp (local radio)';
$value = snmp_get($device, 'locCurrentTemp.0', '-Oqv', 'ExaltComProducts');
if ($value) {
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'extendair', $descr, '1', '1', null, null, null, null, $value);
}

unset(
    $oid,
    $index,
    $descr,
    $value
);
