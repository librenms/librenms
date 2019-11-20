<?php
/**
 * gaia.inc.php
 *
 * LibreNMS storage poller module for Check Point GAIA
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
 * @copyright  2017 Neil Lathwood
 * @author     Neil Lathwood <gh+n@laf.io>
 */

$gaia_data = snmp_get_multi_oid($device, "multiDiskSize.{$storage['storage_index']} multiDiskUsed.{$storage['storage_index']}", '-OUQst', 'CHECKPOINT-MIB');
$storage['size']  = $gaia_data["multiDiskSize.{$storage['storage_index']}"];
$storage['used']   = $gaia_data["multiDiskUsed.{$storage['storage_index']}"];
$storage['free']   = $storage['size'] - $storage['used'];
$storage['units']  = $storage['storage_units'];
unset($gaia_data);
