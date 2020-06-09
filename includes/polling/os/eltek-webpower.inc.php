<?php
/**
 * eltek-webpower.inc.php
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
 * @copyright  2018 Mikael Sipilainen
 * @author     Mikael Sipilainen <mikael.sipilainen@gmail.com>
 */

// ELTEK - A Delta Group Company - https://eltek.com/
// Flatpack series DC Power system with SmartPack controller
// (SmartPack2 V2.x, SmartPack S V2.x and Compack V2.x.
// Also seems to work correctly with SmartPack_1 v.5.2 and SW v.3.10)

// ELTEK-DISTRIBUTED-MIB (version9) needs files SNMPv2-SMI SNMPv2-TC

$output = preg_split("/[\s,]+/", $device['sysDescr']);
$version = $output[1];

$oid = snmp_get_multi($device, ['systemSiteInfoControllerType.0', 'rectifierStatusType.1', 'batteryName.0', 'systemSiteInfoSystemSeriaNum.0'], '-OQUs', 'ELTEK-DISTRIBUTED-MIB');
$features = 'Rectifier type: '.$oid[1]['rectifierStatusType'];
$features .= ', Battery name: '.$oid[0]['batteryName'];
$hardware = $oid[0]['systemSiteInfoControllerType'];
$serial = $oid[0]['systemSiteInfoSystemSeriaNum'];
