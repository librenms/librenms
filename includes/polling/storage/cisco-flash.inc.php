<?php
/**
 * cisco-flash.inc.php
 *
 * LibreNMS storage polling module for Cisco Flash
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
 * @package    LibreNMS
 * @link       https://www.librenms.org
 * @copyright  2022 Félix Bouynot
 * @author     Félix Bouynot <felix.bouynot@setenforce.one>
 */

use LibreNMS\Util\Number;

$oids = array('ciscoFlashPartitionSize.' . $storage['storage_index'], 'ciscoFlashPartitionFreeSpace.' . $storage['storage_index'], 'ciscoFlashPartitionSizeExtended.' . $storage['storage_index'], 'ciscoFlashPartitionFreeSpaceExtended.' . $storage['storage_index']);
$entry = snmp_get_multi($device, $oids, '-OQUs', 'CISCO-FLASH-MIB');
$entry = array_shift($entry);
$storage['size'] = (Number::cast($entry['ciscoFlashPartitionSize']) === 4294967295 ? $entry['ciscoFlashPartitionSizeExtended'] : $entry['ciscoFlashPartitionSize']);
$storage['free'] = (Number::cast($entry['ciscoFlashPartitionFreeSpace']) === 4294967295 ? $entry['ciscoFlashPartitionFreeSpaceExtended'] : $entry['ciscoFlashPartitionFreeSpace']);
$storage['used'] = $storage['size'] - $storage['free'];
$storage['units'] = 1;

unset ($oids, $entry);
