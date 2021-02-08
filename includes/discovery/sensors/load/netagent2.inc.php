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
 * 3 Phase support extension
 * @copyright  2018 Mikael Sipilainen
 * @author     Mikael Sipilainen <mikael.sipilainen@gmail.com>
 */

// Detect type of UPS (Signle-Phase/3 Phase)
    // Number of input lines
    $upsInputNumLines_oid = '.1.3.6.1.2.1.33.1.3.2.0';
    $in_phaseNum = snmp_get($device, $upsInputNumLines_oid, '-Oqv');

// Single-phase system
if ($in_phaseNum == '1') {
    $load_oid = '.1.3.6.1.4.1.935.1.1.1.4.2.3.0';
    $output_load = snmp_get($device, $load_oid, '-Oqv');

    if (! empty($output_load) || $output_load == 0) {
        $type = 'netagent2';
        $index = 0;
        $limit = 100;
        $warnlimit = 80;
        $lowlimit = 0;
        $lowwarnlimit = null;
        $divisor = 1;
        $load = $output_load / $divisor;
        $descr = 'Output load';

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
}

// 3 phase system
if ($in_phaseNum == '3') {
    // Output L1
    $load_oid = '.1.3.6.1.4.1.935.1.1.1.8.3.5.0';
    $output_load = snmp_get($device, $load_oid, '-Oqv');

    if (! empty($output_load) || $output_load == 0) {
        $type = 'netagent2';
        $index = 0;
        $limit = 100;
        $warnlimit = 80;
        $lowlimit = 0;
        $lowwarnlimit = null;
        $divisor = 10;
        $load = $output_load / $divisor;
        $descr = 'Out L1 (R)';

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
    // Output L2
    $load_oid = '.1.3.6.1.4.1.935.1.1.1.8.3.6.0';
    $output_load = snmp_get($device, $load_oid, '-Oqv');

    if (! empty($output_load) || $output_load == 0) {
        $type = 'netagent2';
        $index = 1;
        $limit = 100;
        $warnlimit = 80;
        $lowlimit = 0;
        $lowwarnlimit = null;
        $divisor = 10;
        $load = $output_load / $divisor;
        $descr = 'Out L2 (S)';

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
    // L3 output
    $load_oid = '.1.3.6.1.4.1.935.1.1.1.8.3.7.0';
    $output_load = snmp_get($device, $load_oid, '-Oqv');

    if (! empty($output_load) || $output_load == 0) {
        $type = 'netagent2';
        $index = 2;
        $limit = 100;
        $warnlimit = 80;
        $lowlimit = 0;
        $lowwarnlimit = null;
        $divisor = 10;
        $load = $output_load / $divisor;
        $descr = 'Out L3 (T)';

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
}
