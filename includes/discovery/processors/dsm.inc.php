<?php
/**
 * dsm.inc.php
 *
 * LibreNMS processors module for Synology DSM
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

if ($device['os'] == 'dsm') {
    echo 'Synology DSM : ';
    $oid = '.1.3.6.1.2.1.25.3.3.1.2';
    $procs = snmpwalk_cache_multi_oid($device, $oid, array());
    $x = 0;
    foreach ($procs as $index => $proc) {
        $usage = $proc['hrProcessorLoad'];
        if (is_numeric($usage)) {
            discover_processor($valid['processor'], $device, $oid . '.' . $index, $index, 'dsm', 'Proc #'. $x, '1', $usage);
            $x++;
        }
    }
}
