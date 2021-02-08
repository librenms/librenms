<?php
/**
 * netagent2.inc.php
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
$battery_current_oid = '.1.3.6.1.4.1.935.1.1.1.2.2.7.0';
$battery_current = snmp_get($device, $battery_current_oid, '-Oqv');

if (! empty($battery_current) || $battery_current == 0) {
    $type = 'netagent2';
    $index = 0;
    $limit = 30;
    $warnlimit = null;
    $lowlimit = null;
    $lowwarnlimit = null;
    $divisor = 10;
    $current = $battery_current / $divisor;
    $descr = 'Battery Current';

    discover_sensor(
        $valid['sensor'],
        'current',
        $device,
        $battery_current_oid,
        $index,
        $type,
        $descr,
        $divisor,
        '1',
        $lowlimit,
        $lowwarnlimit,
        $warnlimit,
        $limit,
        $current
    );
}
