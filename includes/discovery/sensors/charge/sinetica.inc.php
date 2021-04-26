<?php
/**
 * sinecta.php
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
$charge_oid = '.1.3.6.1.4.1.13891.101.2.4.0';
$charge = snmp_get($device, $charge_oid, '-Osqnv');

if (! empty($charge)) {
    $type = 'sinetica';
    $index = 0;
    $limit = 100;
    $lowlimit = 0;
    $lowwarnlimit = 10;
    $descr = 'Battery Charge';

    discover_sensor(
        $valid['sensor'],
        'charge',
        $device,
        $charge_oid,
        $index,
        $type,
        $descr,
        1,
        1,
        $lowlimit,
        $lowwarnlimit,
        null,
        $limit,
        $charge
    );
}
