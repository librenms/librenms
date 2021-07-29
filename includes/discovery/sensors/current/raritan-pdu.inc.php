<?php

$divisor = '1000';
$multiplier = '1';

// Check Inlets PDU2 MIB
$inlet_oids = snmp_walk($device, 'inletLabel', '-Osqn', 'PDU2-MIB');
$inlet_oids = trim($inlet_oids);
if ($inlet_oids) {
    d_echo('PDU2 MIB Inlet');
    foreach (explode("\n", $inlet_oids) as $inlet_data) {
        $inlet_data = trim($inlet_data);
        if ($inlet_data) {
            [$inlet_oid,$inlet_descr] = explode(' ', $inlet_data, 2);
            $inlet_split_oid = explode('.', $inlet_oid);
            $inlet_index = $inlet_split_oid[(count($inlet_split_oid) - 2)] . '.' . $inlet_split_oid[(count($inlet_split_oid) - 1)];
            $inlet_oid = ".1.3.6.1.4.1.13742.6.5.2.3.1.4.$inlet_index.1";
            $inlet_divisor = pow(10, snmp_get($device, "inletSensorDecimalDigits.$inlet_index.rmsCurrent", '-Ovq', 'PDU2-MIB'));
            $inlet_current = (snmp_get($device, "measurementsInletSensorValue.$inlet_index.1", '-Ovq', 'PDU2-MIB') / $inlet_divisor);
            if ($inlet_current >= 0) {
                discover_sensor($valid['sensor'], 'current', $device, $inlet_oid, $inlet_index, 'raritan', $inlet_descr, $inlet_divisor, $multiplier, null, null, null, null, $inlet_current);
            }
        }
    }
}

// Check for per-outlet polling PDU-MIB
$outlet_oids = snmp_walk($device, 'outletIndex', '-Osqn', 'PDU-MIB');
$outlet_oids = trim($outlet_oids);
if ($outlet_oids) {
    d_echo('PDU MIB Outlets');
    foreach (explode("\n", $outlet_oids) as $outlet_data) {
        $outlet_data = trim($outlet_data);
        if ($outlet_data) {
            [$outlet_oid,$outlet_descr] = explode(' ', $outlet_data, 2);
            $outlet_split_oid = explode('.', $outlet_oid);
            $outlet_index = $outlet_split_oid[(count($outlet_split_oid) - 1)];
            $outletsuffix = "$outlet_index";
            $outlet_insert_index = $outlet_index;
            // outletLoadValue: "A non-negative value indicates the measured load in milli Amps"
            $outlet_oid = ".1.3.6.1.4.1.13742.4.1.2.2.1.4.$outletsuffix";
            $outlet_descr = snmp_get($device, "outletLabel.$outletsuffix", '-Ovq', 'PDU-MIB');
            $outlet_low_warn_limit = snmp_get($device, "outletCurrentLowerWarning.$outletsuffix", '-Ovq', 'PDU-MIB') / $divisor;
            $outlet_low_limit = snmp_get($device, "outletCurrentLowerCritical.$outletsuffix", '-Ovq', 'PDU-MIB') / $divisor;
            $outlet_high_warn_limit = snmp_get($device, "outletCurrentUpperWarning.$outletsuffix", '-Ovq', 'PDU-MIB') / $divisor;
            $outlet_high_limit = snmp_get($device, "outletCurrentUpperCritical.$outletsuffix", '-Ovq', 'PDU-MIB') / $divisor;
            $outlet_current = snmp_get($device, "outletCurrent.$outletsuffix", '-Ovq', 'PDU-MIB') / $divisor;
            if ($outlet_current >= 0) {
                discover_sensor($valid['sensor'], 'current', $device, $outlet_oid, $outlet_insert_index, 'raritan', $outlet_descr, $divisor, $multiplier, $outlet_low_limit, $outlet_low_warn_limit, $outlet_high_warn_limit, $outlet_high_limit, $outlet_current);
            }
        }
    }
}

// Check for per-outlet polling PDU2-MIB
$outlet_oids = snmp_walk($device, '.1.3.6.1.4.1.13742.6.3.5.3.1.2.1', '-Osqn', 'PDU2-MIB');
$outlet_oids = trim($outlet_oids);
if ($outlet_oids) {
    d_echo('PDU2 MIB Outlets');
    foreach (explode("\n", $outlet_oids) as $outlet_data) {
        $outlet_data = trim($outlet_data);
        if ($outlet_data) {
            [$outlet_oid,$outlet_descr] = explode(' ', $outlet_data, 2);
            $outlet_split_oid = explode('.', $outlet_oid);
            $outlet_index = $outlet_split_oid[(count($outlet_split_oid) - 1)];
            $outletsuffix = "$outlet_index";
            $outlet_insert_index = $outlet_index;
            $outlet_oid = ".1.3.6.1.4.1.13742.6.5.4.3.1.4.1.$outletsuffix.1";
            $outlet_descr = snmp_get($device, "outletName.1.$outletsuffix", '-Ovq', 'PDU2-MIB');
            if (! $outlet_descr) {
                $outlet_descr = 'Outlet ' . $outletsuffix;
            }
            $outlet_low_warn_limit = snmp_get($device, "outletSensorSignedLowerWarningThreshold.1.$outlet_index.rmsCurrent", '-Ovq', 'PDU2-MIB');
            $outlet_low_limit = snmp_get($device, "outletSensorSignedLowerCriticalThreshold.1.$outlet_index.rmsCurrent", '-Ovq', 'PDU2-MIB');
            $outlet_high_warn_limit = snmp_get($device, "outletSensorSignedUpperWarningThreshold.1.$outlet_index.rmsCurrent", '-Ovq', 'PDU2-MIB');
            $outlet_high_limit = snmp_get($device, "outletSensorSignedUpperCriticalThreshold.1.$outlet_index.rmsCurrent", '-Ovq', 'PDU2-MIB');
            $outlet_divisor = pow(10, snmp_get($device, "outletSensorDecimalDigits.1.$outlet_index.rmsCurrent", '-Ovq', 'PDU2-MIB'));
            $outlet_power = (snmp_get($device, "measurementsOutletSensorValue.1.$outlet_index.1", '-Ovq', 'PDU2-MIB') / $outlet_divisor);
            if ($outlet_power >= 0) {
                discover_sensor($valid['sensor'], 'current', $device, $outlet_oid, $outlet_insert_index, 'raritan', $outlet_descr, $outlet_divisor, $multiplier, $outlet_low_limit, $outlet_low_warn_limit, $outlet_high_warn_limit, $outlet_high_limit, $outlet_power);
            }
        }
    }
}

/**
 * raritan.inc.php
 *
 * LibreNMS current sensor discovery module for Raritan
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
 * @copyright  2017 Neil Lathwood
 * @author     Neil Lathwood <gh+n@laf.io>
 */
foreach ($pre_cache['raritan_inletTable'] as $index => $raritan_data) {
    for ($x = 1; $x <= $raritan_data['inletPoleCount']; $x++) {
        $tmp_index = "$index.$x";
        $new_index = "inletPoleCurrent.$tmp_index";
        $oid = '.1.3.6.1.4.1.13742.4.1.21.2.1.3.' . $tmp_index;
        $descr = 'Inlet ' . $pre_cache['raritan_inletPoleTable'][$index][$x]['inletPoleLabel'];
        $divisor = 1000;
        $low_limit = $raritan_data['inletCurrentUpperCritical'] / $divisor;
        $low_warn_limit = $raritan_data['inletCurrentUpperWarning'] / $divisor;
        $warn_limit = $raritan_data['inletCurrentLowerWarning'] / $divisor;
        $high_limit = $raritan_data['inletCurrentLowerCritical'] / $divisor;
        $current = $pre_cache['raritan_inletPoleTable'][$index][$x]['inletPoleCurrent'] / $divisor;
        discover_sensor($valid['sensor'], 'current', $device, $oid, $tmp_index, 'raritan', $descr, $divisor, 1, $low_limit, $low_limit, $warn_limit, $high_limit, $current);
    }
}
