<?php
/**
 * power.inc.php
 *
 * LibreNMS power sensor discovery module for Linux
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
 * @author     Neil Lathwood <neil@lathwood.co.uk>
 */
$temp = snmpwalk_cache_multi_oid($device, 'amperageProbeTableEntry', [], 'MIB-Dell-10892', 'dell');
$cur_oid = '.1.3.6.1.4.1.674.10892.1.600.30.1.6.';

foreach ((array) $temp as $index => $entry) {
    $descr = $entry['amperageProbeLocationName'];
    if ($entry['amperageProbeType'] === 'amperageProbeTypeIsSystemWatts') {
        $divisor = 1;
        (isset($entry['amperageProbeReading'])) ? $value = $entry['amperageProbeReading'] : $value = null;
        (isset($entry['amperageProbeLowerCriticalThreshold'])) ? $lowlimit = $entry['amperageProbeLowerCriticalThreshold'] / $divisor : $lowlimit = null;
        (isset($entry['amperageProbeLowerCriticalThreshold'])) ? $low_warn_limit = $entry['amperageProbeLowerCriticalThreshold'] / $divisor : $low_warn_limit = null;
        (isset($entry['amperageProbeUpperNonCriticalThreshold'])) ? $warnlimit = $entry['amperageProbeUpperNonCriticalThreshold'] / $divisor : $warnlimit = null;
        (isset($entry['amperageProbeUpperCriticalThreshold'])) ? $limit = $entry['amperageProbeUpperCriticalThreshold'] / $divisor : $limit = null;

        discover_sensor(null, 'power', $device, $cur_oid . $index, $index, 'dell', $descr, $divisor, '1', $lowlimit, $low_warn_limit, $warnlimit, $limit, $value, 'snmp', $index);
    }

    unset(
        $descr,
        $value,
        $lowlimit,
        $low_warn_limit,
        $warnlimit,
        $limit
    );
}

unset(
    $temp,
    $cur_oid,
    $index,
    $entry
);
