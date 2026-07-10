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

$deviceModel = Device::find($device['device_id']);

if ($device['os'] == 'sonus-sbc') {
    $pkt_oid_rx = '.1.3.6.1.4.1.2879.2.10.4.1.1.35';
    $pkt_oids_rx = SnmpQuery::device($deviceModel)->walk($pkt_oid_rx)->values();
    $pkt_oid_tx = '.1.3.6.1.4.1.2879.2.10.4.1.1.36';
    $pkt_oids_tx = SnmpQuery::device($deviceModel)->walk($pkt_oid_tx)->values();

    foreach ($pkt_oids_rx as $k => $v) {
        $k_array = explode('.', (string) $k);
        echo 'k_array0  : ' . $k_array[0] . "\n";

        if ($k_array[0] == 'enterprises') {
            $ports_mapping['oid'] = str_replace('enterprises.3.6.1.4.1.2879.2.10.4.1.1.35.', '', $k); //# centos case
            echo "replace 'entreprises' ";
        }
        if ($k_array[0] == 'iso') {
            $ports_mapping['oid'] = str_replace('iso.3.6.1.4.1.2879.2.10.4.1.1.35.', '', $k); //# debian / docker case
            echo "replace 'iso' ";
        }
        if ($k_array[0] == 'SNMPv2-SMI::enterprises') {
            $ports_mapping['oid'] = str_replace('SNMPv2-SMI::enterprises.2879.2.10.4.1.1.35.', '', $k); //# debian / docker case
            echo "replace 'SNMPv2-SMI::enterprises' ";
        }

        $index = $ports_mapping['oid'];

        $device_oid = explode('14.', (string) $index, 2);
        $device_ascii = $device_oid[1];
        $codes_device = explode('.', $device_ascii);
        $device_text = '';

        foreach (array_slice($codes_device, 0) as $code) {
            $device_text .= chr((int) $code);
        }

        $sensor_type = 'Port ' . $device_text . ' - peak Bandwidth Rx';
        $descr = 'Port ' . $device_text . ' - peak Bandwidth Rx';
        $divisor = 1;
        $multiplier = 8;
        $current = $v;
        $devicetype = 'sonus-sbc';
        $group = 'Ports peak Bandwidth Rx';
        $full_oid = $pkt_oid_rx . '.' . $index;
        if (is_numeric($current)) {
            discover_sensor(null, 'bitrate', $device, $full_oid, $sensor_type, $devicetype, $descr, $divisor, $multiplier, null, null, null, null, $current, 'snmp', null, null, null, $group);
        }
    }
    unset($pkt_oids_rx, $index, $sensor_type, $descr, $divisor, $multiplier, $current, $devicetype, $group);

    foreach ($pkt_oids_tx as $k => $v) {
        $k_array = explode('.', (string) $k);
        echo 'k_array0  : ' . $k_array[0] . "\n";

        if ($k_array[0] == 'enterprises') {
            $ports_mapping['oid'] = str_replace('enterprises.3.6.1.4.1.2879.2.10.4.1.1.36.', '', $k); //# centos case
            echo "replace 'entreprises' ";
        }
        if ($k_array[0] == 'iso') {
            $ports_mapping['oid'] = str_replace('iso.3.6.1.4.1.2879.2.10.4.1.1.36.', '', $k); //# debian / docker case
            echo "replace 'iso' ";
        }
        if ($k_array[0] == 'SNMPv2-SMI::enterprises') {
            $ports_mapping['oid'] = str_replace('SNMPv2-SMI::enterprises.2879.2.10.4.1.1.36.', '', $k); //# debian / docker case
            echo "replace 'SNMPv2-SMI::enterprises' ";
        }

        $index = $ports_mapping['oid'];

        $device_oid = explode('14.', (string) $index, 2);
        $device_ascii = $device_oid[1];
        $codes_device = explode('.', $device_ascii);
        $device_text = '';

        foreach (array_slice($codes_device, 0) as $code) {
            $device_text .= chr((int) $code);
        }

        $sensor_type = 'Port ' . $device_text . ' - peak Bandwidth Tx';
        $descr = 'Port ' . $device_text . ' - peak Bandwidth Tx';
        $divisor = 1;
        $multiplier = 8;
        $current = $v;
        $devicetype = 'sonus-sbc';
        $group = 'Ports peak Bandwidth Tx';
        $full_oid = $pkt_oid_tx . '.' . $index;
        if (is_numeric($current)) {
            discover_sensor(null, 'bitrate', $device, $full_oid, $sensor_type, $devicetype, $descr, $divisor, $multiplier, null, null, null, null, $current, 'snmp', null, null, null, $group);
        }
    }
    unset($pkt_oids_tx, $index, $sensor_type, $descr, $divisor, $multiplier, $current, $devicetype, $group);
}
