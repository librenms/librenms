<?php
/**
 * cumulus.inc.php
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
 * @copyright  2018 Ryan Finney
 * @author     https://github.com/theherodied/
 */

$data = snmp_getnext_multi($device, 'entPhysicalDescr entPhysicalSoftwareRev entPhysicalSerialNum', '-OQUs', 'ENTITY-MIB');
$hardware = $data['entPhysicalDescr'];
$serial = $data['entPhysicalSerialNum'];
$version = preg_replace('/^Cumulus Linux /', '', $data['entPhysicalSoftwareRev']);
