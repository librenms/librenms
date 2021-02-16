<?php
/**
 * dell.inc.php
 *
 * LibreNMS voltage sensor discovery module for Linux
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
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */
$temp = snmpwalk_cache_multi_oid($device, 'voltageProbeTable', [], 'MIB-Dell-10892');
$cur_oid = '.1.3.6.1.4.1.674.10892.1.600.20.1.6.';

foreach ((array) $temp as $index => $entry) {
    $descr = $entry['voltageProbeLocationName'];
    if ($entry['voltageProbeType'] != 'voltageProbeTypeIsDiscrete') {
        $divisor = 1000;
        $value = $entry['voltageProbeReading'];
        $lowlimit = $entry['voltageProbeLowerCriticalThreshold'] / $divisor;
        $low_warn_limit = $entry['voltageProbeLowerCriticalThreshold'] / $divisor;
        $warnlimit = $entry['voltageProbeUpperNonCriticalThreshold'] / $divisor;
        $limit = $entry['voltageProbeUpperCriticalThreshold'] / $divisor;

        discover_sensor($valid['sensor'], 'voltage', $device, $cur_oid . $index, $index, 'dell', $descr, $divisor, '1', $lowlimit, $low_warn_limit, $warnlimit, $limit, $value, 'snmp', $index);
    }
}

unset(
    $temp,
    $cur_oid,
    $index,
    $entry
);
