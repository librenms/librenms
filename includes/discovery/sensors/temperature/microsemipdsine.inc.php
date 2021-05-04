<?php
/**
 * microsemipdsine.inc.php
 *
 * LibreNMS temperature sensor discovery module for Microsemi PoE Switches
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

// temperature

$temperature_unit = trim(snmp_get($device, '.1.3.6.1.4.1.7428.1.2.2.1.1.12.1', '-Oqv'), '" ');
$temperature = trim(snmp_get($device, '.1.3.6.1.4.1.7428.1.2.2.1.1.11.1', '-Oqv'), '" ');

if (! empty($temperature_unit) && ! empty($temperature)) {
    // If fahrenheit convert to celsius
    $function = null;
    if ($temperature_unit == '2') {
        $function = 'fahrenheit_to_celsius';
        $temperature = fahrenheit_to_celsius($temperature);
    }

    $divisor = 1;
    $index = '11.1';
    $descr = 'Unit Temperature';
    $type = 'microsemipdsine';
    $oid = '.1.3.6.1.4.1.7428.1.2.2.1.1.11.1';
    $current_value = $temperature / $divisor;

    discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, $type, $descr, $divisor, '1', null, null, null, null, $current_value, 'snmp', null, null, $function);
}
