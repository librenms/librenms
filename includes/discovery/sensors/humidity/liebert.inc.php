<?php
/**
 * liebert.inc.php
 *
 * LibreNMS humidty discovery module for Liebert
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
$lib_data = snmpwalk_cache_oid($device, 'lgpEnvHumidityEntryRel', [], 'LIEBERT-GP-ENVIRONMENTAL-MIB');

foreach ($lib_data as $index => $data) {
    if (is_numeric($data['lgpEnvHumidityMeasurementRelTenths'])) {
        $oid = '.1.3.6.1.4.1.476.1.42.3.4.2.2.3.1.50.' . $index;
        $low_limit = $data['lgpEnvHumidityLowThresholdRelTenths'];
        $high_limit = $data['lgpEnvHumidityHighThresholdRelTenths'];
        $current = $data['lgpEnvHumidityMeasurementRelTenths'];
        $divisor = 10;
        $new_index = 'lgpEnvHumidityMeasurementRelTenths.' . $index;
    } elseif (is_numeric($data['lgpEnvHumidityMeasurementRel'])) {
        $oid = '.1.3.6.1.4.1.476.1.42.3.4.2.2.3.1.3.' . $index;
        $low_limit = $data['lgpEnvHumidityLowThresholdRel'];
        $high_limit = $data['lgpEnvHumidityHighThresholdRel'];
        $current = $data['lgpEnvHumidityMeasurementRel'];
        $divisor = 1;
        $new_index = 'lgpEnvHumidityMeasurementRel.' . $index;
    }

    if (is_numeric($current)) {
        $descr = $data['lgpEnvHumidityDescrRel'];
        discover_sensor($valid['sensor'], 'humidity', $device, $oid, $new_index, 'liebert', $descr, $divisor, 1, $low_limit, null, null, $high_limit, $current / $divisor);
    }
}

unset(
    $lib_data,
    $current,
    $oid,
    $descr,
    $low_limit,
    $high_limit,
    $divisor,
    $new_index
);

$return_humidity = snmp_get($device, 'lgpEnvReturnAirHumidity.0', '-Oqv');
if (is_numeric($return_humidity)) {
    $oid = '.1.3.6.1.4.1.476.1.42.3.4.2.1.2.0';
    $index = 'lgpEnvReturnAirHumidity.0';
    $descr = 'Return Air Humidity';
    discover_sensor($valid['sensor'], 'humidity', $device, $oid, $index, 'liebert', $descr, $divisor, '1', null, null, null, null, $return_humidity);
}

$supply_humidity = snmp_get($device, 'lgpEnvSupplyAirHumidity.0', '-Oqv');
if (is_numeric($supply_humidity)) {
    $oid = '.1.3.6.1.4.1.476.1.42.3.4.2.1.3.0';
    $index = 'lgpEnvSupplyAirHumidity.0';
    $descr = 'Supply Air Humidity';
    discover_sensor($valid['sensor'], 'humidity', $device, $oid, $index, 'liebert', $descr, $divisor, '1', null, null, null, null, $supply_humidity);
}
