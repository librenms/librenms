<?php
/**
 * aos-emu2.inc.php
 *
 * LibreNMS sensors pre-cache discovery module for APC EMU2
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
 * @copyright  2016 Neil Lathwood
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */
echo 'emsStatusSysTempUnits ';
$pre_cache['emu2_temp_scale'] = snmp_get($device, 'emsStatusSysTempUnits.0', '-OQv', 'PowerNet-MIB');

echo 'emsProbeStatusEntry ';
$pre_cache['emu2_temp'] = snmpwalk_cache_oid($device, 'emsProbeStatusEntry', [], 'PowerNet-MIB');
