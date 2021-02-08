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
$ups_temperature_oid = '.1.3.6.1.4.1.935.1.1.1.2.2.3.0';
$ups_temperature = snmp_get($device, $ups_temperature_oid, '-Oqv');

if (! empty($ups_temperature) || $ups_temperature == 0) {
    $type = 'netagent2';
    $index = 0;
    $limit = 110;
    $warnlimit = 50;
    $lowlimit = 0;
    $lowwarnlimit = 6;
    $divisor = 10;
    $temperature = $ups_temperature / $divisor;
    $descr = 'Temperature';

    discover_sensor(
        $valid['sensor'],
        'temperature',
        $device,
        $ups_temperature_oid,
        $index,
        $type,
        $descr,
        $divisor,
        '1',
        $lowlimit,
        $lowwarnlimit,
        $warnlimit,
        $limit,
        $temperature
    );
}
