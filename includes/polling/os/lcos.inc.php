<?php
/**
 * locos.inc.php
 *
 * LibreNMS os polling module for Lancom LCOS
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
 * @copyright  2017 Marcus Pink
 * @author     Marcus Pink <mpink@avantgarde-labs.de>
 */

$lcos_data = snmp_get_multi_oid($device, ['lcsFirmwareVersionTableEntrySerialNumber.1', 'lcsFirmwareVersionTableEntryVersion.1', 'lcsFirmwareVersionTableEntryModule.1'], '-OQs', 'LCOS-MIB');

$serial  = $lcos_data['lcsFirmwareVersionTableEntrySerialNumber.eIfc'];
$version = $lcos_data['lcsFirmwareVersionTableEntryVersion.eIfc'];
$hardware = $lcos_data['lcsFirmwareVersionTableEntryModule.eIfc'];

unset($lcos_data);
