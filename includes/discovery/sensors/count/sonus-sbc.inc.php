<?php

/*
 * LibreNMS discovery module for Sonus SBC Count sensors
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
 * @package    LibreNMS
 * @link       https://www.librenms.org
 *
 * @copyright  2026 Network Solutions Factory
 *
 * @author     Sofia El Khalifi <sofia.elkhalifi@netsf.fr>
 */

$deviceModel = DeviceCache::get($device['device_id']);

if ($device['os'] == 'sonus-sbc') {
    $bw_alarm_oid = '.1.3.6.1.4.1.2879.2.10.4.1.1.41';
    $bw_alarm_count = SnmpQuery::device($deviceModel)->numeric()->walk($bw_alarm_oid)->values();

    foreach ($bw_alarm_count as $k => $v) {
        $device_oid = explode('14.', (string) $k, 2);
        $codes_device = explode('.', $device_oid[1]);
        $device_text = '';

        foreach (array_slice($codes_device, 0) as $code) {
            $device_text .= chr((int) $code);
        }

        $sensor_type = 'Port ' . $device_text . ' - Bandwidth alarm count';
        $descr = $device_text . ' - Bandwidth Alarm passed';
        $divisor = 1;
        $multiplier = 1;
        $devicetype = 'sonus-sbc';
        $group = 'Bandwidth Alarm Count';
        if (is_numeric((int) $v)) {
            discover_sensor(null, 'count', $device, $k, $sensor_type, $devicetype, $descr, $divisor, $multiplier, null, null, null, null, (int) $v, 'snmp', null, null, null, $group);
        }
    }
    unset($bw_alarm_oid, $index, $sensor_type, $descr, $divisor, $multiplier, $current, $devicetype, $group);
}
