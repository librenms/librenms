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

// Config
    // RRD graph color start value
    $index = 0; // Text color number to start increasing +1

    // Voltage levels - EU and UK 230 volts +10% - 6% (ie. between 216.2 volts and 253 volts)
    $limit = 270; // Maximum graph level
    $warnlimit = 253; // Warning limit (High)
    $lowlimit = 210;     // Minimum graph level
    $lowwarnlimit = 216;     // Warning limit (Low)
    $divisor1phase = 10;      // Divisor to set sensor input value (eg. value 2324/10=232,4 Volts)
    $divisor3phase = 10;       // Divisor to set sensor input value (eg. value 22/1=22 Volts)

    // UPS single-phase battery system values
    $bat_1phase_limit = 30; // Remember to check correct values
    $bat_1phase_warnlimit = 28;
    $bat_1phase_lowlimit = 10;
    $bat_1phase_lowwarnlimit = 18;
    $bat_1phase_divisor = 10;

    // UPS 3 phase battery system values
    $bat_3phase_limit = 270; // Remember to check correct values
    $bat_3phase_warnlimit = 270;
    $bat_3phase_lowlimit = 210;
    $bat_3phase_lowwarnlimit = 215;
    $bat_3phase_divisor = 10;

// Detect type of UPS (Signle-Phase/3 Phase)
// Number of input lines
    $upsInputNumLines_oid = '.1.3.6.1.2.1.33.1.3.2.0';
    $in_phaseNum = snmp_get($device, $upsInputNumLines_oid, '-Oqv');

    // Number of output lines
    $upsOutputNumLines_oid = '.1.3.6.1.2.1.33.1.4.3.0';
    $out_phaseNum = snmp_get($device, $upsOutputNumLines_oid, '-Oqv');

// INPUT single-phase system
if ($in_phaseNum == '1') {
    $in_voltage_oid = '.1.3.6.1.4.1.935.1.1.1.3.2.1.0';
    $in_voltage = snmp_get($device, $in_voltage_oid, '-Oqv');

    if (! empty($in_voltage) || $in_voltage == 0) {
        $type = 'netagent2';
        $divisor = $divisor1phase;
        $voltage = $in_voltage / $divisor;
        $descr = 'Input';

        discover_sensor(
            $valid['sensor'],
            'voltage',
            $device,
            $in_voltage_oid,
            $index,
            $type,
            $descr,
            $divisor,
            '1',
            $lowlimit,
            $lowwarnlimit,
            $warnlimit,
            $limit,
            $voltage
        );
    }
}

// INPUT voltage 3 Phase system
if ($in_phaseNum == '3') {
    // Phase L1 (R)
    $in_voltage1_oid = '.1.3.6.1.4.1.935.1.1.1.8.2.2.0';
    $in_voltage1 = snmp_get($device, $in_voltage1_oid, '-Oqv');

    if (! empty($in_voltage1) || $in_voltage1 == 0) {
        $type = 'netagent2';
        $divisor = $divisor3phase;
        $voltage = $in_voltage1 / $divisor;
        $descr = 'In L1';

        discover_sensor(
            $valid['sensor'],
            'voltage',
            $device,
            $in_voltage1_oid,
            $index,
            $type,
            $descr,
            $divisor,
            '1',
            $lowlimit,
            $lowwarnlimit,
            $warnlimit,
            $limit,
            $voltage
        );
    }
    // Phase L2 (S)
    $in_voltage2_oid = '.1.3.6.1.4.1.935.1.1.1.8.2.3.0';
    $in_voltage2 = snmp_get($device, $in_voltage2_oid, '-Oqv');

    if (! empty($in_voltage2) || $in_voltage2 == 0) {
        $type = 'netagent2';
        $index++;
        $divisor = $divisor3phase;
        $voltage = $in_voltage2 / $divisor;
        $descr = 'In L2';

        discover_sensor(
            $valid['sensor'],
            'voltage',
            $device,
            $in_voltage2_oid,
            $index,
            $type,
            $descr,
            $divisor,
            '1',
            $lowlimit,
            $lowwarnlimit,
            $warnlimit,
            $limit,
            $voltage
        );
    }
    // Phase L3 (T)
    $in_voltage3_oid = '.1.3.6.1.4.1.935.1.1.1.8.2.4.0';
    $in_voltage3 = snmp_get($device, $in_voltage3_oid, '-Oqv');

    if (! empty($in_voltage3) || $in_voltage3 == 0) {
        $type = 'netagent2';
        $index++;
        $divisor = $divisor3phase;
        $voltage = $in_voltage3 / $divisor;
        $descr = 'In L3';

        discover_sensor(
            $valid['sensor'],
            'voltage',
            $device,
            $in_voltage3_oid,
            $index,
            $type,
            $descr,
            $divisor,
            '1',
            $lowlimit,
            $lowwarnlimit,
            $warnlimit,
            $limit,
            $voltage
        );
    }
}

// OUTPUT voltage single-phase
if ($in_phaseNum == '1') {
    $out_voltage_oid = '.1.3.6.1.4.1.935.1.1.1.4.2.1.0';
    $out_voltage = snmp_get($device, $out_voltage_oid, '-Oqv');

    if (! empty($out_voltage) || $out_voltage == 0) {
        $type = 'netagent2';
        $index++;
        $divisor = $divisor1phase;
        $voltage = $out_voltage / $divisor;
        $descr = 'Output';

        discover_sensor(
            $valid['sensor'],
            'voltage',
            $device,
            $out_voltage_oid,
            $index,
            $type,
            $descr,
            $divisor,
            '1',
            $lowlimit,
            $lowwarnlimit,
            $warnlimit,
            $limit,
            $voltage
        );
    }
}

// OUTPUT voltage 3 Phase system
if ($out_phaseNum == '3') {
    // Phase L1 (R)
    $out_voltage1_oid = '.1.3.6.1.4.1.935.1.1.1.8.3.2.0';
    $out_voltage1 = snmp_get($device, $out_voltage1_oid, '-Oqv');

    if (! empty($out_voltage1) || $out_voltage1 == 0) {
        $type = 'netagent2';
        $index++;
        $divisor = $divisor3phase;
        $voltage = $out_voltage1 / $divisor;
        $descr = 'Out L1';

        discover_sensor(
            $valid['sensor'],
            'voltage',
            $device,
            $out_voltage1_oid,
            $index,
            $type,
            $descr,
            $divisor,
            '1',
            $lowlimit,
            $lowwarnlimit,
            $warnlimit,
            $limit,
            $voltage
        );
    }
    // Phase L2 (S)
    $out_voltage2_oid = '.1.3.6.1.4.1.935.1.1.1.8.3.3.0';
    $out_voltage2 = snmp_get($device, $out_voltage2_oid, '-Oqv');

    if (! empty($out_voltage2) || $out_voltage2 == 0) {
        $type = 'netagent2';
        $index++;
        $divisor = $divisor3phase;
        $voltage = $out_voltage2 / $divisor;
        $descr = 'Out L2';

        discover_sensor(
            $valid['sensor'],
            'voltage',
            $device,
            $out_voltage2_oid,
            $index,
            $type,
            $descr,
            $divisor,
            '1',
            $lowlimit,
            $lowwarnlimit,
            $warnlimit,
            $limit,
            $voltage
        );
    }
    // Phase L3 (T)
    $out_voltage3_oid = '.1.3.6.1.4.1.935.1.1.1.8.3.4.0';
    $out_voltage3 = snmp_get($device, $out_voltage3_oid, '-Oqv');

    if (! empty($out_voltage3) || $out_voltage3 == 0) {
        $type = 'netagent2';
        $index++;
        $divisor = $divisor3phase;
        $voltage = $out_voltage3 / $divisor;
        $descr = 'Out L3';

        discover_sensor(
            $valid['sensor'],
            'voltage',
            $device,
            $out_voltage3_oid,
            $index,
            $type,
            $descr,
            $divisor,
            '1',
            $lowlimit,
            $lowwarnlimit,
            $warnlimit,
            $limit,
            $voltage
        );
    }
}

// Bypass voltage 3 Phase system
if ($out_phaseNum == '3') {
    // Phase L1 (R)
    $bypass_voltage1_oid = '.1.3.6.1.4.1.935.1.1.1.8.4.2.0';
    $bypass_voltage1 = snmp_get($device, $bypass_voltage1_oid, '-Oqv');

    if (! empty($bypass_voltage1) || $bypass_voltage1 == 0) {
        $type = 'netagent2';
        $index++;
        $divisor = $divisor3phase;
        $voltage = $bypass_voltage1 / $divisor;
        $descr = 'Bypass L1';

        discover_sensor(
            $valid['sensor'],
            'voltage',
            $device,
            $bypass_voltage1_oid,
            $index,
            $type,
            $descr,
            $divisor,
            '1',
            $lowlimit,
            $lowwarnlimit,
            $warnlimit,
            $limit,
            $voltage
        );
    }
    // Phase L2 (S)
    $bypass_voltage2_oid = '.1.3.6.1.4.1.935.1.1.1.8.4.3.0';
    $bypass_voltage2 = snmp_get($device, $bypass_voltage2_oid, '-Oqv');

    if (! empty($bypass_voltage2) || $bypass_voltage2 == 0) {
        $type = 'netagent2';
        $index++;
        $divisor = $divisor3phase;
        $voltage = $bypass_voltage2 / $divisor;
        $descr = 'Bypass L2';

        discover_sensor(
            $valid['sensor'],
            'voltage',
            $device,
            $bypass_voltage2_oid,
            $index,
            $type,
            $descr,
            $divisor,
            '1',
            $lowlimit,
            $lowwarnlimit,
            $warnlimit,
            $limit,
            $voltage
        );
    }
    // Phase L3 (T)
    $bypass_voltage3_oid = '.1.3.6.1.4.1.935.1.1.1.8.4.4.0';
    $bypass_voltage3 = snmp_get($device, $bypass_voltage3_oid, '-Oqv');

    if (! empty($bypass_voltage3) || $bypass_voltage3 == 0) {
        $type = 'netagent2';
        $index++;
        $divisor = $divisor3phase;
        $voltage = $bypass_voltage3 / $divisor;
        $descr = 'Bypass L3';

        discover_sensor(
            $valid['sensor'],
            'voltage',
            $device,
            $bypass_voltage3_oid,
            $index,
            $type,
            $descr,
            $divisor,
            '1',
            $lowlimit,
            $lowwarnlimit,
            $warnlimit,
            $limit,
            $voltage
        );
    }
}

// BATTERY Voltage
// Set divisor and limit ranges 1 phase UPS systems
if ($in_phaseNum == '1') {
    $battery_voltage1_oid = '.1.3.6.1.4.1.935.1.1.1.2.2.2.0';
    $battery_voltage1 = snmp_get($device, $battery_voltage1_oid, '-Oqv');
    $limit = $bat_1phase_limit;
    $warnlimit = $bat_1phase_warnlimit;
    $lowlimit = $bat_1phase_lowlimit;
    $lowwarnlimit = $bat_1phase_lowwarnlimit;
    $divisor = $bat_1phase_divisor;
}

// Set divisor and limit ranges 3 phase UPS systems
if ($in_phaseNum == '3') {
    $battery_voltage1_oid = '.1.3.6.1.2.1.33.1.2.5.0';
    $battery_voltage1 = snmp_get($device, $battery_voltage1_oid, '-Oqv');
    $limit = $bat_3phase_limit;
    $warnlimit = $bat_3phase_warnlimit;
    $lowlimit = $bat_3phase_lowlimit;
    $lowwarnlimit = $bat_3phase_lowwarnlimit;
    $divisor = $bat_3phase_divisor;
}

if (! empty($battery_voltage1) || $battery_voltage1 == 0) {
    $type = 'netagent2';
    $index++;
    $voltage = $battery_voltage1 / $divisor;
    $descr = 'Battery';

    discover_sensor(
        $valid['sensor'],
        'voltage',
        $device,
        $battery_voltage1_oid,
        $index,
        $type,
        $descr,
        $divisor,
        '1',
        $lowlimit,
        $lowwarnlimit,
        $warnlimit,
        $limit,
        $voltage
    );
}
