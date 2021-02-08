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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 * @copyright  2017 Lorenzo Zafra
 * @author     Lorenzo Zafra<zafra@ualberta.ca>
 */

// System Voltage
$systemVoltage = trim(snmp_get($device, 'systemVoltage.0', '-Oqv', 'ICT-DISTRIBUTION-PANEL-MIB'), '" ');

if (! empty($systemVoltage)) {
    $divisor = 1;
    $oid = '.1.3.6.1.4.1.39145.10.6.0';
    $index = 0;
    $descr = 'System Voltage';
    $type = 'ict-pdu';
    $current_value = $systemVoltage / $divisor;

    discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current_value);
}
