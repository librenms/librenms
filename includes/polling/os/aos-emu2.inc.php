<?php
/**
 * aos-emu2.inc.php
 *
 * LibreNMS os discovery module for APC EMU2
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
 * @copyright  2016 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */

$aos_emu2_data = snmp_get_multi_oid($device, ['emsIdentSerialNumber.0', 'emsIdentProductNumber.0', 'emsIdentHardwareRev.0', 'emsIdentFirmwareRev.0'], '-OQUs', 'PowerNet-MIB');

$serial   = trim($aos_emu2_data['emsIdentSerialNumber.0'], '"');
$hardware = trim($aos_emu2_data['emsIdentProductNumber.0'], '"') . ' ' . trim($aos_emu2_data['emsIdentHardwareRev.0'], '"');
$version  = trim($aos_emu2_data['emsIdentFirmwareRev.0'], '"');

unset($aos_emu2_data);
