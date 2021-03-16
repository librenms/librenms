<?php
/**
 * tegile.inc.php
 *
 * LibreNMS storage polling module for Tegile Storage
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
 * @copyright  2018 Ryan Finney
 * @author     https://github.com/theherodied/
 */
if (! is_array($storage_cache['vrp'])) {
    $storage_cache['vrp'] = snmpwalk_cache_oid($device, 'hwStorageEntry', null, 'HUAWEI-FLASH-MAN-MIB');
    d_echo($storage_cache);
}
$entry = $storage_cache['vrp'][$storage['storage_index']];
$storage['units'] = $storage['storage_units'];
$storage['size'] = $entry['hwStorageSpace'] * $storage['units'];
$storage['free'] = $entry['hwStorageSpaceFree'] * $storage['units'];
$storage['used'] = $storage['size'] - $storage['free'];
