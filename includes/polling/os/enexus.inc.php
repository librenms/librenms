<?php
/**
 * enexus.inc.php
 *
 * -Description-
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
 * @copyright  2017 Barry O'Donovan
 * @author     BArry O'Donovan <barry@lightnet.ie>
 */

$hardware = snmp_get($device, 'powerSystemModel.0', '-Ovqa', 'SP2-MIB');
$sw_version1 = snmp_get($device, 'controlUnitSwVersion.1', '-Ovqa', 'SP2-MIB');
$sw_version2 = snmp_get($device, 'controlUnitSwVersion.2', '-Ovqa', 'SP2-MIB');
if (!empty($sw_version1)) {
    $version = $sw_version1;
} elseif (!empty($sw_version2)) {
    $version = $sw_version2;
}
$serial = snmp_get($device, 'powerSystemSerialNumber.0', '-Ovqa', 'SP2-MIB');
