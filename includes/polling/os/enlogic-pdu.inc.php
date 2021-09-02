<?php
/**
 * enlogic-pdu.inc.php
 *
 * LibreNMS os polling module for enLogic PDU
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
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */
$tmp_enlogic = snmp_get_multi_oid($device, 'pduNamePlateModelNumber pduNamePlateSerialNumber pduNamePlateFirmwareVersion', '-OUQn', 'ENLOGIC-PDU-MIB');

$hardware = $tmp_enlogic['.1.3.6.1.4.1.38446.1.1.2.1.10.1'];
$serial = $tmp_enlogic['.1.3.6.1.4.1.38446.1.1.2.1.11.1'];
$version = $tmp_enlogic['.1.3.6.1.4.1.38446.1.1.2.1.13.1'];

unset($tmp_enlogic);
