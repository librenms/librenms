<?php
/**
 * hiveos-wireless.inc.php
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

$data = snmp_get_multi_oid($device, ['ahSystemSerial.0', 'ahDeviceMode.0', 'ahFirmwareVersion.0'], '-OQUs', 'AH-SYSTEM-MIB');
$hardware = $data['ahDeviceMode.0'];
$version2 = $data['ahFirmwareVersion.0'];
// Version has 'HiveOS ' included. We want to remove it so OS doesn't show HiveOS twice.
$version = preg_replace('/^HiveOS /', '', $version2);
$serial = $data['ahSystemSerial.0'];
