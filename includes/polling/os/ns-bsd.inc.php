<?php
/**
 * ns-bsd.inc.php
 *
 * LibreNMS os poller module for Technicolor TG MediaAccess devices
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
 * @copyright  2017 Thomas GAGNIERE
 * @author     Thomas GAGNIERE <tgagniere@reseau-concept.com>
 */
$data = snmp_get_multi_oid($device, ['snsModel.0', 'snsVersion.0', 'snsSerialNumber.0', 'snsSystemName.0'], '-OUQs', 'STORMSHIELD-PROPERTY-MIB');

$hardware = $data['snsModel.0'];
$version = $data['snsVersion.0'];
$serial = $data['snsSerialNumber.0'];
$sysName = $data['snsSystemName.0'];
