<?php

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
 *
 * @copyright  2017 Neil Lathwood
 * @author     Neil Lathwood <gh+n@laf.io>
 */
$divisor = 1000;
$multiplier = 1;

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
            $outlet_index = $outlet_split_oid[count($outlet_split_oid) - 1];
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
                discover_sensor(null, 'current', $device, $outlet_oid, $outlet_insert_index, 'raritan', $outlet_descr, $divisor, $multiplier, $outlet_low_limit, $outlet_low_warn_limit, $outlet_high_warn_limit, $outlet_high_limit, $outlet_current);
            }
        }
    }
}

foreach ($pre_cache['raritan_inletTable'] as $index => $raritan_data) {
    for ($x = 1; $x <= $raritan_data['inletPoleCount']; $x++) {
        $tmp_index = "$index.$x";
        $new_index = "inletPoleCurrent.$tmp_index";
        $oid = '.1.3.6.1.4.1.13742.4.1.21.2.1.3.' . $tmp_index;
        $descr = 'Inlet ' . $pre_cache['raritan_inletPoleTable'][$index][$x]['inletPoleLabel'];
        $divisor = 1000;
        $low_limit = isset($raritan_data['inletCurrentLowerCritical']) ? $raritan_data['inletCurrentLowerCritical'] / $divisor : null;
        $low_warn_limit = isset($raritan_data['inletCurrentLowerWarning']) ? $raritan_data['inletCurrentLowerWarning'] / $divisor : null;
        $warn_limit = isset($raritan_data['inletCurrentUpperWarning']) ? $raritan_data['inletCurrentUpperWarning'] / $divisor : null;
        $high_limit = isset($raritan_data['inletCurrentUpperCritical']) ? $raritan_data['inletCurrentUpperCritical'] / $divisor : null;
        $current = $pre_cache['raritan_inletPoleTable'][$index][$x]['inletPoleCurrent'] / $divisor;
        discover_sensor(null, 'current', $device, $oid, $tmp_index, 'raritan', $descr, $divisor, 1, $low_limit, $low_warn_limit, $warn_limit, $high_limit, $current);
    }
}
