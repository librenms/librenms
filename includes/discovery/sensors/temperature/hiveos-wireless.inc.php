<?php
/**
 * hiveos-wireless.inc.php
 *
 * LibreNMS temperature sensor discovery module for Hiveos-Wireless
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
 * @copyright  2018 Ryan Finney
 * @author     https://github.com/theherodied/
 */

$oid = ".1.3.6.1.4.1.26928.1.2.10.0";
$index = 0;
$sensor_type = ' hiveoswirelessEnvTemp';
$descr = 'Env Temp';
$divisor = 1;
$temperature = (snmp_get($device, $oid, '-Oqv', 'AH-SYSTEM-MIB') / $divisor);
if (is_numeric($temperature)) {
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, $sensor_type, $descr, $divisor, null, null, null, null, null, $temperature);
}
