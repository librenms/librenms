<?php
/**
 * timos.inc.php
 *
 * LibreNMS processor discovery module for TiMOS
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

if ($device['os'] === 'timos') {
    echo 'TiMOS CPU: ';
    $descr = 'Processor';
    $oid = '.1.3.6.1.4.1.6527.3.1.2.1.1.1.0';
    $usage = snmp_get($device, $oid, '-Ovqn');
    if (is_numeric($usage)) {
        discover_processor($valid['processor'], $device, $oid, '0', 'timos', $descr, '1', $usage, null, null);
    }
}
