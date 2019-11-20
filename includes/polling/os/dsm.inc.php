<?php
/**
 * dsm.inc.php
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
 * @copyright  2018 Nick Peelman
 * @author     Nick Peelman <nick@peelman.us>
 */

$tmp_dsm  = snmp_get_multi_oid($device, ['modelName.0', 'version.0', 'serialNumber.0'], '-OUQs', 'SYNOLOGY-SYSTEM-MIB');
$hardware = $tmp_dsm['modelName.0'];
$version  = $tmp_dsm['version.0'];
$serial   = $tmp_dsm['serialNumber.0'];
unset($tmp_dsm);
