<?php
/**
 * saf-integra.inc.php
 *
 * LibreNMS processor discovery module for Saf Integra
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

if ($device['os'] === 'saf-integra') {
    echo 'Saf Integra : ';

    $oid = '.1.3.6.1.4.1.7571.100.1.1.7.2.4.10.0';
    $descr = 'Processor';
    $usage = snmp_get($device, $oid, '-Ovqn');

    if (is_numeric($usage)) {
        $usage = 100 - ($usage / 10);
        discover_processor($valid['processor'], $device, $oid, '0', 'saf-integra', $descr, '1', $usage);
    }
}

unset(
    $oid,
    $descr,
    $usage
);
