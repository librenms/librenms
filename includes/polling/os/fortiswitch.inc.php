<?php
/**
 * fortiswitch.inc.php
 *
 * LibreNMS os polling module for FortiSwitch
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
 * @copyright  2016 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */

$temp_data = explode(' ', snmp_get($device, 'fsSysVersion.0', '-Onvq', 'FORTINET-FORTISWITCH-MIB'));
$hardware = $temp_data[0];
$version = $temp_data[1];
$serial = preg_replace('/"/', '', snmp_get($device, 'fnSysSerial.0', '-Onvq', 'FORTINET-CORE-MIB'));
