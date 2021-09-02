<?php
/**
 * netonix.inc.php
 *
 * LibreNMS power module for Netonix
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
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */
echo 'Netonix: ';

$power_oid = '.1.3.6.1.4.1.46242.6.0'; // NETONIX-SWITCH-MIB::totalPowerConsumption
$power_value = snmp_get($device, $power_oid, '-Oqv');
$descr = 'Total Consumption';
$divisor = 10;

if (is_numeric($power_value) && $power_value > 0) {
    discover_sensor($valid['sensor'], 'power', $device, $power_oid, 0, $device['os'], $descr, $divisor, 1, null, null, null, null, $power_value / $divisor);
}
