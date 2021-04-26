<?php
/**
 * raritan-pdu.inc.php
 *
 * LibreNMS temperature sensor discovery module for Raritan
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
$index = 'unitCpuTemp.0';
$oid = '.1.3.6.1.4.1.13742.4.1.3.1.5.0';
$descr = 'Processor Temp';
$divisor = 10;
$raritan_data = snmp_get_multi_oid($device, ['unitCpuTemp.0', 'unitTempLowerWarning.0', 'unitTempLowerCritical.0', 'unitTempUpperWarning.0', 'unitTempUpperCritical.0'], '-OUQs', 'PDU-MIB');
if (is_array($raritan_data) && ! empty($raritan_data)) {
    $low_limit = $raritan_data['unitTempLowerCritical.0'];
    $low_warn_limit = $raritan_data['unitTempLowerWarning.0'];
    $warn_limit = $raritan_data['unitTempUpperWarning.0'];
    $high_limit = $raritan_data['unitTempUpperCritical.0'];
    $current = $raritan_data['unitCpuTemp.0'] / $divisor;
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, $tmp_index, 'raritan', $descr, $divisor, 1, $low_limit, $low_limit, $warn_limit, $high_limit, $current);
} else {
    $multiplier = '1';
    foreach ($pre_cache['raritan_extSensorConfig'] as $index => $data) {
        if ($data['externalSensorType'] == 'temperature') {
            $descr = $data['externalSensorName'];
            $oid = ".1.3.6.1.4.1.13742.6.5.5.3.1.4.$index";
            $low_limit = ($data['externalSensorLowerCriticalThreshold'] / $divisor);
            $low_warn_limit = ($data['externalSensorLowerWarningThreshold'] / $divisor);
            $high_limit = ($data['externalSensorUpperCriticalThreshold'] / $divisor);
            $high_warn_limit = ($data['externalSensorUpperWarningThreshold'] / $divisor);

            $measure_data = $pre_cache['raritan_extSensorMeasure'][$index];
            $current = ($measure_data['measurementsExternalSensorValue'] / $divisor);
            $sensor_available = $measure_data['measurementsExternalSensorIsAvailable'];
            $raritan_temp_scale = $data['externalSensorUnits'];
            $user_func = null;
            if ($raritan_temp_scale == 'degreeF') {
                $low_warn_limit = fahrenheit_to_celsius($low_warn_limit, $raritan_temp_scale);
                $low_limit = fahrenheit_to_celsius($low_limit, $raritan_temp_scale);
                $high_warn_limit = fahrenheit_to_celsius($high_warn_limit, $raritan_temp_scale);
                $high_limit = fahrenheit_to_celsius($high_limit, $raritan_temp_scale);
                $current = fahrenheit_to_celsius($current, $raritan_temp_scale);
                $user_func = 'fahrenheit_to_celsius';
            }
            if (is_numeric($current) && $current >= 0 && $sensor_available === 'true') {
                discover_sensor($valid['sensor'], 'temperature', $device, $oid, 'measurementsExternalSensorValue.' . $index, 'raritan', $descr, $divisor, $multiplier, $low_limit, $low_warn_limit, $high_warn_limit, $high_limit, $current, 'snmp', null, null, $user_func);
            }
        }
    }
}

//Check for PDU MIB external Sensors
$oids = snmpwalk_cache_multi_oid($device, 'externalSensorTable', [], 'PDU-MIB');
$offset = 0;
foreach ($oids as $index => $sensor) {
    if ($sensor['externalSensorType'] == 'temperature') {
        $oid = ".1.3.6.1.4.1.13742.4.3.3.1.41.$index";
        $descr = $sensor['externalSensorName'];
        $temp_current = $sensor['externalSensorValue'];
        $temp_current = $temp_current / $divisor;
        $limit_high = $sensor['externalSensorUpperWarningThreshold'] / $divisor;
        $limit_low = $sensor['externalSensorLowerWarningThreshold'] / $divisor;
        $limit_high_warn = $sensor['externalSensorUpperCriticalThreshold'] / $divisor;
        $limit_low_warn = $sensor['externalSensorLowerCriticalThreshold'] / $divisor;
        $offset++;
        if (is_numeric($temp_current) && $temp_current >= 0) {
            discover_sensor($valid['sensor'], 'temperature', $device, $oid, $offset, 'raritan', $descr, $divisor, 1, $limit_low, $limit_low_warn, $limit_high_warn, $limit_high, $temp_current);
        }
    }
}
