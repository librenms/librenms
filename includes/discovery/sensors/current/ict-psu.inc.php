<?php
/**
 * ict-psu.inc.php
 *
 * LibreNMS current sensor discovery module for ICT Digital Series Power Supply
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

// Output Current
// SNMPv2-SMI::enterprises.39145.11.8.0 = STRING: "0.4" -- outputCurrent

$outputCurrent = trim(snmp_get($device, 'outputCurrent.0', '-Oqv', 'ICT-DIGITAL-SERIES-MIB'), '" ');
if (! empty($outputCurrent)) {
    $divisor = 1;
    $index = 0;
    $oid = '.1.3.6.1.4.1.39145.11.8.0';
    $descr = 'Output Current';
    $type = 'ict-psu';
    $currentValue = $outputCurrent / $divisor;

    discover_sensor($valid['sensor'], 'current', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $currentValue);
}
