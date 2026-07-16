<?php

/**
 * ./includes/polling/os/sonus-sbc.inc.php
 *
 * LibreNMS ports module for Sonus SBC
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
 * @copyright  2026 Sofia El Khalifi
 * @author     Sofia El Khalifi <sofia.elkhalifi@netsf.fr>
 */

$deviceModel = DeviceCache::get($device['device_id']);

$EthPortSpeed = [
    '1' => '10000000',
    '2' => '100000000',
    '3' => '1000000000',
    '4' => 'unknown',
    '5' => '10000000000',
    '6' => '0',
    '7' => '2500000000',
    '8' => '5000000000',
    '9' => '20000000000',
    '10' => '25000000000',
    '11' => '40000000000',
    '12' => '50000000000',
    '13' => '56000000000',
    '14' => '100000000000',
    '15' => '200000000000',
];

$snmp = [];
$ports_mapping = [];
$snmp['ports_group'] = SnmpQuery::device($deviceModel)->numeric()->walk('.1.3.6.1.4.1.2879.2.10.4.2.1.3')->values();
$snmp['mgmt_group'] = SnmpQuery::device($deviceModel)->numeric()->walk('.1.3.6.1.4.1.2879.2.10.4.3.1.5')->values();
$snmp['speed_group'] = SnmpQuery::device($deviceModel)->numeric()->walk('.1.3.6.1.4.1.2879.2.10.4.1.1.4')->values();
$snmp['ctx_if'] = SnmpQuery::device($deviceModel)->numeric()->walk('.1.3.6.1.4.1.2879.2.10.2.10.1.5')->values();

foreach ($snmp['speed_group'] as $k => $v) {
    $v = explode(',', (string) $v);
    $oid_index = explode('14.', $k, 2);
    $port_oid = explode('.4.', $oid_index[1], 2);

    $device_ascii = $port_oid[0];
    $port_ascii = $port_oid[1];

    $codes_port = explode('.', $port_ascii);
    $port_text = '';

    foreach ($codes_port as $code) {
        $port_text .= chr((int) $code);
    }

    $codes_device = explode('.', $device_ascii);
    $device_text = '';

    foreach ($codes_device as $code) {
        $device_text .= chr((int) $code);
    }

    $device_index = substr($device_ascii, -1);
    $port_index = substr($port_ascii, -1);
    $index = $device_index . '.' . $port_index;

    $port_stats[$index]['ifDescr'] = $device_text . '/' . $port_text;
    $port_stats[$index]['ifAlias'] = $device_text . '/' . $port_text;
    $speed = SnmpQuery::device($deviceModel)->get('.1.3.6.1.4.1.2879.2.10.4.1.1.4.14.' . $oid_index[1])->value();
    $port_stats[$index]['ifSpeed'] = $EthPortSpeed[(string) $speed];

    $phy_status = SnmpQuery::device($deviceModel)->get('.1.3.6.1.4.1.2879.2.10.4.5.1.10.14.' . $device_ascii . '.8.112.114.103.95.' . $port_ascii)->value();
    $redundancy_status = SnmpQuery::device($deviceModel)->get('.1.3.6.1.4.1.2879.2.10.4.5.1.13.14.' . $device_ascii . '.8.112.114.103.95.' . $port_ascii)->value();

    if ($phy_status == 1 && $redundancy_status == 2) {
        $port_stats[$index]['ifAdminStatus'] = 'up';
        $port_stats[$index]['ifOperStatus'] = 'up';
    } else {
        $port_stats[$index]['ifAdminStatus'] = 'down';
        $port_stats[$index]['ifOperStatus'] = 'down';
    }

    $port_stats[$index]['ifOutOctets'] = SnmpQuery::device($deviceModel)->get('.1.3.6.1.4.1.2879.2.10.4.1.1.34.14.' . $oid_index[1])->value();
    $port_stats[$index]['ifInOctets'] = SnmpQuery::device($deviceModel)->get('.1.3.6.1.4.1.2879.2.10.4.1.1.33.14.' . $oid_index[1])->value();
}

foreach ($snmp['ports_group'] as $k => $v) {
    $v = explode(',', (string) $v);
    $oid_index = explode("'.2.1.3.', $k);
    $port_oid = explode('4.', $oid_index[1], 2);
    $port_ascii = $port_oid[1];
    $codes = explode('.', $port_ascii);
    $port_text = '';

    foreach ($codes as $code) {
        $port_text .= chr((int) $code);
    }

    $index = substr($port_ascii, -1);

    $port_stats[$index]['ifDescr'] = $port_text;
    $port_stats[$index]['ifAlias'] = $port_text;

    $port_stats[$index]['ifOutOctets'] = SnmpQuery::device($deviceModel)->get('.1.3.6.1.4.1.2879.2.10.4.2.1.3.' . $oid_index[1])->value();
    $port_stats[$index]['ifInOctets'] = SnmpQuery::device($deviceModel)->get('.1.3.6.1.4.1.2879.2.10.4.2.1.1.' . $oid_index[1])->value();

    $port_stats[$index]['ifOutUcastPkts'] = SnmpQuery::device($deviceModel)->get('.1.3.6.1.4.1.2879.2.10.4.2.1.6.' . $oid_index[1])->value();
    $port_stats[$index]['ifInUcastPkts'] = SnmpQuery::device($deviceModel)->get('.1.3.6.1.4.1.2879.2.10.4.2.1.5.' . $oid_index[1])->value();

    $port_stats[$index]['ifOutErrors'] = SnmpQuery::device($deviceModel)->get('.1.3.6.1.4.1.2879.2.10.4.2.1.7.' . $oid_index[1])->value();
    $port_stats[$index]['ifInErrors'] = SnmpQuery::device($deviceModel)->get('.1.3.6.1.4.1.2879.2.10.4.2.1.8.' . $oid_index[1])->value();

    $port_stats[$index]['ifOutDiscards'] = SnmpQuery::device($deviceModel)->get('.1.3.6.1.4.1.2879.2.10.4.2.1.10.' . $oid_index[1])->value();
    $port_stats[$index]['ifInDiscards'] = SnmpQuery::device($deviceModel)->get('.1.3.6.1.4.1.2879.2.10.4.2.1.9.' . $oid_index[1])->value();

    $status = DB::raw('SELECT `ifOperStatus` FROM `ports` WHERE `device_id` = ? AND `ifDescr` LIKE ?', [$device['device_id'], '%/' . $port_text]);
    $up = true;

    foreach ($status as $s) {
        if ($s != 'up') {
            $up = false;
            break;
        }
    }

    if ($up) {
        $port_stats[$index]['ifAdminStatus'] = 'up';
        $port_stats[$index]['ifOperStatus'] = 'up';
    } else {
        $port_stats[$index]['ifAdminStatus'] = 'down';
        $port_stats[$index]['ifOperStatus'] = 'down';
    }
}

foreach ($snmp['mgmt_group'] as $k => $v) {
    $v = explode(',', (string) $v);
    $oid_index = explode('14.', $k, 2);
    $port_oid = explode('.4.', $oid_index[1], 2);
    $device_ascii = $port_oid[0];
    $port_ascii = $port_oid[1];

    $codes_port = explode('.', $port_ascii);
    $port_text = '';

    foreach ($codes_port as $code) {
        $port_text .= chr((int) $code);
    }

    $codes_device = explode('.', $device_ascii);
    $device_text = '';

    foreach ($codes_device as $code) {
        $device_text .= chr((int) $code);
    }

    $device_index = substr($device_ascii, -1);
    $port_index = substr($port_ascii, -1);
    $index = $device_index . '.' . $port_index;

    $port_stats[$index]['ifDescr'] = $device_text . '/' . $port_text;
    $port_stats[$index]['ifAlias'] = $device_text . '/' . $port_text;

    $speed = SnmpQuery::device($deviceModel)->get('.1.3.6.1.4.1.2879.2.10.4.3.1.5.14.' . $oid_index[1])->value();
    $port_stats[$index]['ifSpeed'] = $EthPortSpeed[(string) $speed];

    $phy_status = SnmpQuery::device($deviceModel)->get('.1.3.6.1.4.1.2879.2.10.4.5.1.10.14.' . $device_ascii . '.8.112.114.103.95.' . $port_ascii)->value();
    $redundancy_status = SnmpQuery::device($deviceModel)->get('.1.3.6.1.4.1.2879.2.10.4.5.1.13.14.' . $device_ascii . '.8.112.114.103.95.' . $port_ascii)->value();

    if ($phy_status == 1 && $redundancy_status == 2) {
        $port_stats[$index]['ifAdminStatus'] = 'up';
        $port_stats[$index]['ifOperStatus'] = 'up';
    } else {
        $port_stats[$index]['ifAdminStatus'] = 'down';
        $port_stats[$index]['ifOperStatus'] = 'down';
    }

    $port_stats[$index]['ifOutOctets'] = SnmpQuery::device($deviceModel)->get('.1.3.6.1.4.1.2879.2.10.4.3.1.15.14.' . $oid_index[1])->value();
    $port_stats[$index]['ifInOctets'] = SnmpQuery::device($deviceModel)->get('.1.3.6.1.4.1.2879.2.10.4.3.1.14.' . $oid_index[1])->value();
}

foreach ($snmp['ctx_if'] as $k => $v) {
    $v = explode(',', (string) $v);
    $oid_index = explode('.1.5.', $k);
    $port_oid = explode('.9.', $oid_index[1], 2);
    $port_ascii = $port_oid[1];

    $codes_port = explode('.', $port_ascii);
    $port_text = '';

    foreach ($codes_port as $code) {
        $port_text .= chr((int) $code);
    }

    $index = substr($port_ascii, -1);

    $port_stats[$index]['ifDescr'] = $port_text;
    $port_stats[$index]['ifAlias'] = $port_text;

    $oper_status = SnmpQuery::device($deviceModel)->get('.1.3.6.1.4.1.2879.2.10.2.10.1.3.' . $oid_index[1])->value();

    if ($oper_status == 0) {
        $port_stats[$index]['ifAdminStatus'] = null;
        $port_stats[$index]['ifOperStatus'] = null;
    } elseif ($oper_status == 1) {
        $port_stats[$index]['ifAdminStatus'] = 'up';
        $port_stats[$index]['ifOperStatus'] = 'down';
    } elseif ($oper_status == 2) {
        $port_stats[$index]['ifAdminStatus'] = 'down';
        $port_stats[$index]['ifOperStatus'] = 'down';
    } elseif ($oper_status == 6) {
        $port_stats[$index]['ifAdminStatus'] = 'up';
        $port_stats[$index]['ifOperStatus'] = 'up';
    }

    $port_stats[$index]['ifInUcastPkts'] = SnmpQuery::device($deviceModel)->get('.1.3.6.1.4.1.2879.2.10.2.10.1.5.' . $oid_index[1])->value();
    $port_stats[$index]['ifOutUcastPkts'] = SnmpQuery::device($deviceModel)->get('.1.3.6.1.4.1.2879.2.10.2.10.1.6.' . $oid_index[1])->value();
}
