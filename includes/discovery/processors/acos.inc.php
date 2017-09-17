<?php
/**
 * acos.inc.php
 *
 * LibreNMS processors discovery module for A10 ACOS
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

if ($device['os'] === 'acos') {
    echo 'ACOS: ';
    $acos_procs = snmpwalk_group($device, 'axSysCpuTable', 'A10-AX-MIB');
    foreach ($acos_procs as $proc_index => $proc_info) {
        $usage = $proc_info['axSysCpuUsageValue'];
        if (is_numeric($usage)) {
            $descr = "Proc #$proc_index";
            discover_processor($valid['processor'], $device, ".1.3.6.1.4.1.22610.2.4.1.3.2.1.3.$proc_index", $proc_index, 'acos', $descr, '1', $usage, null, null);
        }
    }
}
