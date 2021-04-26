<?php
/**
 * liebert.inc.php
 *
 * LibreNMS sensors (current) discovery module for Liebert PDUs
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
 * @copyright  2019 Spencer Butler
 * @author     Spencer Butler <github@crooked.app>
 */
$entPhysicalIndex = null;
$entPhysicalIndex_measured = null;
$user_func = null;
$group = null;
$class = 'current';
$poller_type = 'snmp';

$psline_data = snmpwalk_cache_oid($device, 'lgpPduPsLineTable', [], 'LIEBERT-GP-PDU-MIB', 'liebert');
$ec_input_rated = snmp_getnext($device, 'lgpPduPsEntryEcInputRated', '-OUqsev', 'LIEBERT-GP-PDU-MIB', 'liebert');

foreach (array_keys($psline_data) as $index) {
    $low_limit_p = $psline_data[$index]['lgpPduPsLineEntryEcThrshldUndrAlarm'] / 100;
    $high_warn_limit_p = $psline_data[$index]['lgpPduPsLineEntryEcThrshldOvrWarn'] / 100;
    $high_limit_p = $psline_data[$index]['lgpPduPsLineEntryEcThrshldOvrAlarm'] / 100;

    $oid = '.1.3.6.1.4.1.476.1.42.3.8.30.40.1.22.' . $index;
    $type = 'liebert';
    $descr = 'Total Input Line ' . $psline_data[$index]['lgpPduPsLineEntryId'];
    $divisor = 100;
    $multiplier = 1;

    $low_limit = $ec_input_rated * $low_limit_p;
    $low_warn_limit = null;
    $high_warn_limit = $ec_input_rated * $high_warn_limit_p;
    $high_limit = $ec_input_rated * $high_limit_p;

    $current = $psline_data[$index]['lgpPduPsLineEntryEcHundredths'];

    discover_sensor(
        $valid['sensor'],
        $class,
        $device,
        $oid,
        $index . 'lgpPduPsLineEntryEcHundredths',
        $type,
        $descr,
        $divisor,
        $multiplier,
        $low_limit,
        $low_warn_limit,
        $high_warn_limit,
        $high_limit,
        $current,
        $poller_type,
        $entPhysicalIndex,
        $entPhysicalIndex_measured,
        $user_func,
        $group
    );
}
unset($psline_data);

$ps_data = snmpwalk_cache_oid($device, 'lgpPduPsTable', [], 'LIEBERT-GP-PDU-MIB', 'liebert', '-OQUse');

foreach (array_keys($ps_data) as $index) {
    $high_warn_limit_p = $ps_data[$index]['lgpPduPsEntryEcNeutralThrshldOvrWarn'] / 100;
    $high_limit_p = $ps_data[$index]['lgpPduPsEntryEcNeutralThrshldOvrAlarm'] / 100;

    $oid = '.1.3.6.1.4.1.476.1.42.3.8.30.20.1.70.' . $index;
    $type = 'liebert';
    $descr = 'Neutral ' . $ps_data[$index]['lgpPduPsLineEntryId'];
    $divisor = 10;
    $multiplier = 1;

    $low_limit = null;
    $low_warn_limit = null;
    $high_warn_limit = $ec_input_rated * $high_warn_limit_p;
    $high_limit = $ec_input_rated * $high_limit_p;

    $current = $ps_data[$index]['lgpPduPsEntryEcNeutral'];

    discover_sensor(
        $valid['sensor'],
        $class,
        $device,
        $oid,
        $index . 'lgpPduPsEntryEcNeutral',
        $type,
        $descr,
        $divisor,
        $multiplier,
        $low_limit,
        $low_warn_limit,
        $high_warn_limit,
        $high_limit,
        $current,
        $poller_type,
        $entPhysicalIndex,
        $entPhysicalIndex_measured,
        $user_func,
        $group
    );
}
unset($ps_data);

$rb_data = snmpwalk_cache_oid($device, 'lgpPduRbTable', [], 'LIEBERT-GP-PDU-MIB', 'liebert', '-OQUse');

foreach (array_keys($rb_data) as $index) {
    $low_limit_p = $rb_data[$index]['lgpPduRbEntryEcThrshldUndrAlm'] / 100;
    $high_warn_limit_p = $rb_data[$index]['lgpPduRbEntryEcThrshldOvrWarn'] / 100;
    $high_limit_p = $rb_data[$index]['lgpPduRbEntryEcThrshldOvrAlm'] / 100;

    $oid = '.1.3.6.1.4.1.476.1.42.3.8.40.20.1.130.' . $index;
    $type = 'liebert';
    $descr = 'RMS ' . $rb_data[$index]['lgpPduRbEntryUsrLabel'];
    $divisor = 100;
    $multiplier = 1;

    $low_limit = $ec_input_rated * $low_limit_p;
    $low_warn_limit = null;
    $high_warn_limit = $ec_input_rated * $high_warn_limit_p;
    $high_limit = $ec_input_rated * $high_limit_p;

    $current = $rb_data[$index]['lgpPduRbEntryEcHundredths'];
    $group = 'Line to Neutral';

    discover_sensor(
        $valid['sensor'],
        $class,
        $device,
        $oid,
        $index . 'lgpPduRbEntryEcHundredths',
        $type,
        $descr,
        $divisor,
        $multiplier,
        $low_limit,
        $low_warn_limit,
        $high_warn_limit,
        $high_limit,
        $current,
        $poller_type,
        $entPhysicalIndex,
        $entPhysicalIndex_measured,
        $user_func,
        $group
    );
}
unset($rb_data);
