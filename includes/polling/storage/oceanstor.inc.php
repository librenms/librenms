<?php
/**
 * oceanstor.inc.php
 *
 * LibreNMS storage polling module for Huawei OceanStor
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
 * @copyright  2017 Neil Lathwood
 * @author     Neil Lathwood <gh+n@laf.io>
 */
$oceanstor_tmp = snmp_get_multi_oid($device, ['usedCapacity.0', 'totalCapacity.0'], '-OUQs', 'ISM-STORAGE-SVC-MIB');

$storage['size'] = $oceanstor_tmp['totalCapacity.0'];
$storage['used'] = $oceanstor_tmp['usedCapacity.0'];
$storage['free'] = ($storage['size'] - $storage['used']);
$storage['units'] = 1024;
unset($oceanstor_tmp);
