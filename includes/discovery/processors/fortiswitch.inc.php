<?php
/**
 * fortiswitch.inc.php
 *
 * LibreNMS processor discovery module for FortiSwitch
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

if ($device['os'] === 'fortiswitch') {
    echo 'Fortiswitch : ';
    $descr = 'Processor';
    $usage = snmp_get($device, 'fsSysCpuUsage.0', '-Ovq', 'FORTINET-FORTISWITCH-MIB');

    if (is_numeric($usage)) {
        discover_processor($valid['processor'], $device, '.1.3.6.1.4.1.12356.106.4.1.2.0', '1', 'fortiswitch', $descr, '1', $usage, null, null);
    }
}

unset($usage);
