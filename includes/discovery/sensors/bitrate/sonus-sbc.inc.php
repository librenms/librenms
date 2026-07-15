<?php

/*
 * LibreNMS discovery module for Sonus SBC bitrate sensors
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

use App\Models\Device;

$deviceModel = DeviceCache::get($device['device_id']);

if ($device['os'] == 'sonus-sbc') {
    $pkt_oid_rx = '.1.3.6.1.4.1.2879.2.10.4.1.1.35';
    $pkt_oids_rx = SnmpQuery::device($deviceModel)->numeric()->walk($pkt_oid_rx)->values();
    $pkt_oid_tx = '.1.3.6.1.4.1.2879.2.10.4.1.1.36';
    $pkt_oids_tx = SnmpQuery::device($deviceModel)->numeric()->walk($pkt_oid_tx)->values();

    foreach ($pkt_oids_rx as $k => $v) {
        $device_oid = explode('14.', (string) $k, 2);
        $codes_device = explode('.', $device_oid[1]);
        $device_text = '';

        foreach (array_slice($codes_device, 0) as $code) {
            $device_text .= chr((int) $code);
        }

        $sensor_type = 'Port ' . $device_text . ' - peak Bandwidth Rx';
        $descr = 'Port ' . $device_text . ' - peak Bandwidth Rx';
        $divisor = 1;
        $multiplier = 8;
        $devicetype = 'sonus-sbc';
        $group = 'Ports';
        if (is_numeric($v)) {
            discover_sensor(null, 'bitrate', $device, $k, $sensor_type, $devicetype, $descr, $divisor, $multiplier, null, null, null, null, $v, 'snmp', null, null, null, $group);
        }
    }
    unset($pkt_oids_rx, $index, $sensor_type, $descr, $divisor, $multiplier, $current, $devicetype, $group);

    foreach ($pkt_oids_tx as $k => $v) {
        $device_oid = explode('14.', (string) $k, 2);
        $codes_device = explode('.', $device_oid[1]);
        $device_text = '';

        foreach (array_slice($codes_device, 0) as $code) {
            $device_text .= chr((int) $code);
        }

        $sensor_type = 'Port ' . $device_text . ' - peak Bandwidth Tx';
        $descr = 'Port ' . $device_text . ' - peak Bandwidth Tx';
        $divisor = 1;
        $multiplier = 8;
        $devicetype = 'sonus-sbc';
        $group = 'Ports';
        if (is_numeric($v)) {
            discover_sensor(null, 'bitrate', $device, $k, $sensor_type, $devicetype, $descr, $divisor, $multiplier, null, null, null, null, $v, 'snmp', null, null, null, $group);
        }
    }
    unset($pkt_oids_tx, $index, $sensor_type, $descr, $divisor, $multiplier, $current, $devicetype, $group);
}
