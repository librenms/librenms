<?php
/**
 * timos.inc.php
 *
 * LibreNMS current discovery module for Nokia SROS (formerly TimOS)
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
$divisor = 50000;
foreach ($pre_cache['timos_oids'] as $index => $entry) {
    if (is_numeric($entry['tmnxDDMTxBiasCurrent']) && $entry['tmnxDDMTxBiasCurrent'] != 0 && $entry['tmnxDDMTxBiasCurrentLowAlarm'] != 0) {
        $oid = '.1.3.6.1.4.1.6527.3.1.2.2.4.31.1.11.' . $index;
        $value = $entry['tmnxDDMTxBiasCurrent'] / $divisor;
        $entPhysicalIndex = $index;
        $entPhysicalIndex_measured = 'ports';

        $limit_low = $entry['tmnxDDMTxBiasCurrentLowAlarm'] / $divisor;
        $warn_limit_low = $entry['tmnxDDMTxBiasCurrentLowWarning'] / $divisor;
        $limit = $entry['tmnxDDMTxBiasCurrentHiAlarm'] / $divisor;
        $warn_limit = $entry['tmnxDDMTxBiasCurrentHiWarning'] / $divisor;

        $port_descr = get_port_by_index_cache($device['device_id'], str_replace('1.', '', $index));
        $descr = $port_descr['ifName'] . ' Tx Current';

        discover_sensor($valid['sensor'], 'dbm', $device, $oid, 'tx-' . $index, 'timos', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $value, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured, $user_func);
    }
}

foreach ($pre_cache['timos_lanes_oids'] as $index => $entry) {
    if (is_numeric($entry['tmnxDDMLaneTxBiasCurrent']) && $entry['tmnxDDMLaneTxBiasCurrent'] != 0 && $entry['tmnxDDMLaneTxBiasCurrentLowAlarm'] != 0) {
        $oid = '.1.3.6.1.4.1.6527.3.1.2.2.4.66.1.7.' . $index;
        $value = $entry['tmnxDDMLaneTxBiasCurrent'] / $divisor;

        $entPhysicalIndex = $index;
        $entPhysicalIndex_measured = 'ports';

        $limit_low = $entry['tmnxDDMLaneTxBiasCurrentLowAlarm'] / $divisor;
        $warn_limit_low = $entry['tmnxDDMLaneTxBiasCurrentLowWarn'] / $divisor;
        $limit = $entry['tmnxDDMLaneTxBiasCurrentHiAlarm'] / $divisor;
        $warn_limit = $entry['tmnxDDMLaneTxBiasCurrentHiWarn'] / $divisor;

        $port_descr = get_port_by_index_cache($device['device_id'], str_replace(['1.','.1','.2','.3','.4'], '', $index));
        $descr = $port_descr['ifName'] . '/' . end(explode('.', $index)) . ' Tx Current';

        discover_sensor($valid['sensor'], 'current', $device, $oid, 'biascurrent-' . $index, 'junos', $descr, $divisor, $multiplier, $limit_low, $warn_limit_low, $warn_limit, $limit, $value, 'snmp', $entPhysicalIndex, $entPhysicalIndex_measured);
    }
}
