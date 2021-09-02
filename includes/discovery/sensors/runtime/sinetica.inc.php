<?php
/**
 * sinetica.inc.php
 *
 * -Description-
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
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */
$runtime_oid = '.1.3.6.1.4.1.13891.101.2.3.0';
$runtime = snmp_get($device, $runtime_oid, '-Osqvt');

if (! empty($runtime)) {
    $type = 'sinetcia';
    $index = '2.3.0';
    $descr = 'Runtime';
    $low_limit = 5;
    $low_limit_warn = 10;
    discover_sensor($valid['sensor'], 'runtime', $device, $runtime_oid, $index, $type, $descr, 1, 1, $low_limit, $low_limit_warn, null, null, $runtime);
}
