<?php
/**
 * ees.inc.php
 *
 * LibreNMS os polling module for Emerson
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
$tmp_ees = snmp_get_multi($device, ['identControllerFirmwareVersion.0', 'identControllerSerialNumber.0'], '-OQUs', 'EES-POWER-MIB');
$version = $tmp_ees[0]['identControllerFirmwareVersion'];
$serial = $tmp_ees[0]['identControllerSerialNumber'];

unset($tmp_ees);
