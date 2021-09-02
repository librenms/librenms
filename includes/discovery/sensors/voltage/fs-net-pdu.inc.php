<?php
/**
 * fs-net-pdu.inc.php
 *
 * LibreNMS current discovery module for FS.com NET-PDU
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
$oid = '.1.3.6.1.4.1.30966.10.3.2.1.0';
$voltage = snmp_get($device, $oid, '-Oqv');
if ($voltage > 0) {
    discover_sensor($valid['sensor'], 'voltage', $device, $oid, 0, 'PDU L1', 'Voltage', 1, 1, null, null, null, null, $voltage);
}
