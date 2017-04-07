<?php
/**
 * ict-pdu.inc.php
 *
 * LibreNMS voltage sensor discovery module for ICT DC Distribution Panel
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
 * @copyright  2017 Lorenzo Zafra
 * @author     Lorenzo Zafra<zafra@ualberta.ca>
 */

// System Voltage
$systemVoltage_oid = '.1.3.6.1.4.1.39145.10.6.0';
$systemVoltage = trim(snmp_get($device, $systemVoltage_oid, '-Oqv'), '" ');

if (!empty($systemVoltage)) {
    $divisor = 1;
    $index = '10.6.0';
    $descr = 'System Voltage';
    $type = 'ict-pdu';
    $current_value = $systemVoltage / $divisor;

    discover_sensor($valid['sensor'], 'voltage', $device, $systemVoltage_oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current_value);
}
