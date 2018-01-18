<?php
/**
 * awplus.inc.php
 *
 * LibreNMS processor discovery module for Alliedware Plus
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
 * @author     Matt Read <matt.read@alliedtelesis.co.nz>
 */

if ($device['os'] === 'awplus') {
    echo 'AWPlus: ';
    $awplus_procs = snmpwalk_group($device, 'cpuUtilisationStackEntry', 'AT-SYSINFO-MIB');
    foreach ($awplus_procs as $proc_index => $proc_info) {
        $usage = $proc_info['cpuUtilisationStackAvgLastMinute'];
        if (is_numeric($usage)) {
            $descr = "Processor $proc_index";
            discover_processor($valid['processor'], $device, ".1.3.6.1.4.1.207.8.4.4.3.3.8.1.4.$proc_index", $proc_index, 'awplus', $descr, '1', $usage, null, null);
        }
    }
}
