<?php
/**
 * infinera-groove.inc.php
 *
 * LibreNMS os poller module for Infinera Groove
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
 * @copyright  2019 Nick Hilliard
 * @author     Nick Hilliard <nick@foobar.org>
 */

echo 'infinera-groove: ';

$oid_list = [
    'neType.0',
    'softwareloadSwloadState.1',
    'softwareloadSwloadState.2',
    'softwareloadSwloadVersion.1',
    'softwareloadSwloadVersion.2',
    'inventoryManufacturerNumber.shelf.1.0.0.0',
];

$data = snmp_get_multi($device, $oid_list, '-OUQs', 'CORIANT-GROOVE-MIB');

foreach (array_keys($data) as $dkey) {
    if (isset($data[$dkey]['softwareloadSwloadState']) && $data[$dkey]['softwareloadSwloadState'] == 'active') {
        $version = $data[$dkey]['softwareloadSwloadVersion'];
        break;
    }
}
$hardware   = $data[0]['neType'];
$serial     = $data['shelf.1.0.0.0']['inventoryManufacturerNumber'];
