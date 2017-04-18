<?php
/**
 * eltex-olt.inc.php
 *
 * LibreNMS processor discovery module for Eltex OLT
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
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */

if ($device['os'] === 'eltex-olt') {
    $descr = 'Processor';
    $proc_usage = snmp_get($device, 'ltp8xCPULoadAverage5Minutes.0', '-Ovq', 'ELTEX-LTP8X-STANDALONE') / 10;
    if (is_numeric($proc_usage)) {
        discover_processor($valid['processor'], $device, '.1.3.6.1.4.1.35265.1.22.1.10.4.0', '0', 'eltex-olt', $descr, '1', $proc_usage);
    }
}
