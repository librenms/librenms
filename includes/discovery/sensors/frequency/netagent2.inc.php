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
 * Original file
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
    // Input frequency
    $in_frequency_oid = '.1.3.6.1.4.1.935.1.1.1.3.2.4.0';
    $in_frequency = snmp_get($device, $in_frequency_oid, '-Oqv');

    if (! empty($in_frequency) || $in_frequency == 0) {
        $type = 'netagent2';
        $index = 0;
        $limit = 60;
        $warnlimit = 51;
        $lowlimit = 0;
        $lowwarnlimit = 49;
        $divisor = 10;
        $frequency = $in_frequency / $divisor;
        $descr = 'Input frequency';

        discover_sensor(
            $valid['sensor'],
            'frequency',
            $device,
            $in_frequency_oid,
            $index,
            $type,
            $descr,
            $divisor,
            '1',
            $lowlimit,
            $lowwarnlimit,
            $warnlimit,
            $limit,
            $frequency
        );
    }
    // Output Frequency
    $out_frequency_oid = '.1.3.6.1.4.1.935.1.1.1.4.2.2.0';
    $out_frequency = snmp_get($device, $frequency_oid, '-Oqv');

    if (! empty($out_frequency) || $out_frequency == 0) {
        $type = 'netagent2';
        $index = 1;
        $limit = 60;
        $warnlimit = 51;
        $lowlimit = 0;
        $lowwarnlimit = 49;
        $divisor = 10;
        $frequency = $out_frequency / $divisor;
        $descr = 'Output frequency';

        discover_sensor(
            $valid['sensor'],
            'frequency',
            $device,
            $out_frequency_oid,
            $index,
            $type,
            $descr,
            $divisor,
            '1',
            $lowlimit,
            $lowwarnlimit,
            $warnlimit,
            $limit,
            $frequency
        );
    }
}

// 3 phase system
if ($in_phaseNum == '3') {
    // Input frequency
    $in_frequency_oid = '.1.3.6.1.4.1.935.1.1.1.8.2.1.0';
    $in_frequency = snmp_get($device, $in_frequency_oid, '-Oqv');

    if (! empty($in_frequency) || $in_frequency == 0) {
        $type = 'netagent2';
        $index = 0;
        $limit = 60;
        $warnlimit = 51;
        $lowlimit = 0;
        $lowwarnlimit = 49;
        $divisor = 10;
        $frequency = $in_frequency / $divisor;
        $descr = 'Input frequency';

        discover_sensor(
            $valid['sensor'],
            'frequency',
            $device,
            $in_frequency_oid,
            $index,
            $type,
            $descr,
            $divisor,
            '1',
            $lowlimit,
            $lowwarnlimit,
            $warnlimit,
            $limit,
            $frequency
        );
    }
    // Output frequency
    $in_frequency_oid = '.1.3.6.1.4.1.935.1.1.1.8.3.1.0';
    $in_frequency = snmp_get($device, $in_frequency_oid, '-Oqv');

    if (! empty($in_frequency) || $in_frequency == 0) {
        $type = 'netagent2';
        $index = 1;
        $limit = 60;
        $warnlimit = 51;
        $lowlimit = 0;
        $lowwarnlimit = 49;
        $divisor = 10;
        $frequency = $in_frequency / $divisor;
        $descr = 'Output frequency';

        discover_sensor(
            $valid['sensor'],
            'frequency',
            $device,
            $in_frequency_oid,
            $index,
            $type,
            $descr,
            $divisor,
            '1',
            $lowlimit,
            $lowwarnlimit,
            $warnlimit,
            $limit,
            $frequency
        );
    }
    // Bypass frequency
    $in_frequency_oid = '.1.3.6.1.4.1.935.1.1.1.8.4.1.0';
    $in_frequency = snmp_get($device, $in_frequency_oid, '-Oqv');

    if (! empty($in_frequency) || $in_frequency == 0) {
        $type = 'netagent2';
        $index = 2;
        $limit = 60;
        $warnlimit = 51;
        $lowlimit = 0;
        $lowwarnlimit = 49;
        $divisor = 10;
        $frequency = $in_frequency / $divisor;
        $descr = 'Bypass frequency';

        discover_sensor(
            $valid['sensor'],
            'frequency',
            $device,
            $in_frequency_oid,
            $index,
            $type,
            $descr,
            $divisor,
            '1',
            $lowlimit,
            $lowwarnlimit,
            $warnlimit,
            $limit,
            $frequency
        );
    }
}
