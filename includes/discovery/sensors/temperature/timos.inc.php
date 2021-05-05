<?php
/**
 * timos.inc.php
 *
 * LibreNMS port temperature discovery module for Nokia SROS (formerly TimOS)
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
 * @copyright  2021 Nick Peelman
 * @author     Nick Peelman <nick@peelman.us>
 */

$multiplier = 1;
$divisor = 100;
foreach ($pre_cache['timos_oids'] as $index => $entry) {
    if (is_numeric($entry['tmnxDDMTemperature']) && $entry['tmnxDDMTemperature'] != 0) {
        $oid = '.1.3.6.1.4.1.6527.3.1.2.2.4.31.1.1.' . $index;
        $value = $entry['tmnxDDMTemperature'] / $divisor;

        $entPhysicalIndex = $index;
        $entPhysicalIndex_measured = 'ports';

        $limit_low = $entry['tmnxDDMTempLowAlarm'] / $divisor;
        $warn_limit_low = $entry['tmnxDDMTempLowWarning'] / $divisor;
        $limit = $entry['tmnxDDMTempHiAlarm'] / $divisor;
        $warn_limit = $entry['tmnxDDMTempHiWarning'] / $divisor;
        $port_descr = get_port_by_index_cache($device['device_id'], str_replace('1.', '', $index));
        $descr = $port_descr['ifName'];

        discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'timos', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $value, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
    }
}
