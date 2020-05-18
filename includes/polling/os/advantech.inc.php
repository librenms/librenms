<?php
/**
 * advantech.inc.php
 *
 * LibreNMS os poller module for Advantech
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
 * @copyright  2020 Mikkel Mondrup Kristensen
 * @author     Mikkel Mondrup Kristensen <mikkel@tdx.dk>
 */

$version = snmp_get($device, 'sysImageVersion.0', '-OQva', 'ADVANTECH-COMMON-MIB', 'advantech');
$hardware = snmp_get($device, 'sysModuleID.0', '-OQva', 'ADVANTECH-COMMON-MIB', 'advantech');
