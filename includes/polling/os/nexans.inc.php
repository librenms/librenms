<?php
/**
 * nexans.inc.php
 *
 * LibreNMS os poller module for HPE iPDU
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
 * @copyright  2019 PipoCanaja
 * @author     PipoCanaja
 */

$oids = ['infoMgmtFirmwareVersion.0', 'infoDescr.0'];

$data = snmp_get_multi_oid($device, $oids, '-OQUs', '+NEXANS-BM-MIB');

$version = $data['infoMgmtFirmwareVersion.0'];
$hardware = $data['infoDescr.0'];
