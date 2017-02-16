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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2016 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

$load_oid = '.1.3.6.1.4.1.935.1.1.1.4.2.3.0';
$output_load = snmp_get($device, $load_oid, '-Oqv');

if (!empty($output_load) || $output_load == 0) {
    $type           = 'netagent2';
    $index          = 0;
    $limit          = 100;
    $warnlimit      = 80;
    $lowlimit       = 0;
    $lowwarnlimit   = null;
    $divisor        = 1;
    $load           = $output_load / $divisor;
    $descr          = 'Output load';

    discover_sensor(
        $valid['sensor'],
        'load',
        $device,
        $load_oid,
        $index,
        $type,
        $descr,
        $divisor,
        '1',
        $lowlimit,
        $lowwarnlimit,
        $warnlimit,
        $limit,
        $load
    );
}
