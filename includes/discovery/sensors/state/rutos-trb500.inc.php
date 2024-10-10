<?php

/* Copyright (C) 2024 Martin Kukal <martin.kukal@jmnet.cz>
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
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

if ($device['os'] == 'rutos-trb500') {
    $oidsSimState = snmpwalk_cache_oid($device, 'mSimState', [], 'TELTONIKA-TRB500-MIB', '-OteQUsb');
    $oidsConnectionState = snmpwalk_cache_oid($device, 'mConnectionState', [], 'TELTONIKA-TRB500-MIB', '-OteQUsb');
    $oidsPinState = snmpwalk_cache_oid($device, 'mPinState', [], 'TELTONIKA-TRB500-MIB', '-OteQUsb');
    $oidsNetState = snmpwalk_cache_oid($device, 'mNetState', [], 'TELTONIKA-TRB500-MIB', '-OteQUsb');
    $oidsNetworkType = snmpwalk_cache_oid($device, 'mNetworkType', [], 'TELTONIKA-TRB500-MIB', '-OteQUsb');

    $simStateMapping = [
        'inserted' => 1,
        'not inserted' => 2,
        'unknown' => 3,
    ];
    $connectionStateMapping = [
        'Connected' => 1,
        'Disconnected' => 2,
        'Unknown' => 3,
    ];

    $pinStateMapping = [
        'SIM not inserted' => 1,
        'Not ready' => 2,
        'OK' => 3,
        'Required PIN' => 4,
        'Required PUK' => 5,
        'Required network personalization password' => 6,
        'Required network personalization unlocking password' => 7,
        'Required network subset personalization password' => 8,
        'Required network subset personalization unlocking password' => 9,
        'SIM failure' => 10,
        'SIM busy' => 11,
        'PUK' => 12,
        'Unknown' => 13,
    ];

    $netStateMapping = [
        'Unregistered' => 1,
        'Registered, home' => 2,
        'Searching' => 3,
        'Denied' => 4,
        'Unknown' => 5,
        'Registered, roaming' => 6,
        'Not supported' => 7,
        'Registered, emergency services only' => 8,
    ];

    $networkTypeMapping = [
        'Auto' => 1,
        'No service' => 2,
        '2G' => 3,
        'GSM' => 4,
        'GPRS' => 5,
        'EDGE' => 6,
        '3G' => 7,
        'WCDMA' => 8,
        'TDSCDMA' => 9,
        'CDMA' => 10,
        'EVDO' => 11,
        'CDMA-EVDO' => 12,
        'HSDPA' => 13,
        'HSUPA' => 14,
        'HSPA+' => 15,
        'EHRPD' => 16,
        'HDR' => 17,
        'UMTS' => 18,
        'HSDPA+HSUPA' => 19,
        '4G' => 20,
        'LTE' => 21,
        '5G' => 22,
        'NR5G' => 23,
        '5G-NSA' => 24,
        '5G-SA' => 25,
        'CAT-M1' => 26,
        'CAT-NB' => 27,
        '2G_3G' => 28,
        'GSM_WCDMA' => 29,
        '2G_4G' => 30,
        'GSM_LTE' => 31,
        '3G_4G' => 32,
        'WCDMA_LTE' => 33,
        '3G_5G' => 34,
        'WCDMA_NR5G' => 35,
        '4G_5G' => 36,
        'LTE_NR5G' => 37,
        'Unknown' => 38,
    ];

    $simState = [
        ['value' => 1, 'generic' => 0, 'graph' => 1, 'descr' => 'inserted'],
        ['value' => 2, 'generic' => 2, 'graph' => 1, 'descr' => 'not inserted'],
        ['value' => 3, 'generic' => 3, 'graph' => 1, 'descr' => 'unknown'],
    ];

    $connectionState = [
        ['value' => 1, 'generic' => 0, 'graph' => 1, 'descr' => 'Connected'],
        ['value' => 2, 'generic' => 2, 'graph' => 1, 'descr' => 'Disconnected'],
        ['value' => 3, 'generic' => 3, 'graph' => 1, 'descr' => 'Unknown'],
    ];

    $pinState = [
        ['value' => 1, 'generic' => 2, 'graph' => 1, 'descr' => 'SIM not inserted'],
        ['value' => 2, 'generic' => 1, 'graph' => 1, 'descr' => 'Not ready'],
        ['value' => 3, 'generic' => 0, 'graph' => 1, 'descr' => 'OK'],
        ['value' => 4, 'generic' => 1, 'graph' => 1, 'descr' => 'Required PIN'],
        ['value' => 5, 'generic' => 1, 'graph' => 1, 'descr' => 'Required PUK'],
        ['value' => 6, 'generic' => 1, 'graph' => 1, 'descr' => 'Required network personalization password'],
        ['value' => 7, 'generic' => 1, 'graph' => 1, 'descr' => 'Required network personalization unlocking password'],
        ['value' => 8, 'generic' => 1, 'graph' => 1, 'descr' => 'Required network subset personalization password'],
        ['value' => 9, 'generic' => 1, 'graph' => 1, 'descr' => 'Required network subset personalization unlocking password'],
        ['value' => 10, 'generic' => 2, 'graph' => 1, 'descr' => 'SIM failure'],
        ['value' => 11, 'generic' => 1, 'graph' => 1, 'descr' => 'SIM busy'],
        ['value' => 12, 'generic' => 1, 'graph' => 1, 'descr' => 'PUK'],
        ['value' => 13, 'generic' => 3, 'graph' => 1, 'descr' => 'Unknown'],
    ];

    $netState = [
        ['value' => 1, 'generic' => 1, 'graph' => 1, 'descr' => 'Unregistered'],
        ['value' => 2, 'generic' => 0, 'graph' => 1, 'descr' => 'Registered, home'],
        ['value' => 3, 'generic' => 1, 'graph' => 1, 'descr' => 'Searching'],
        ['value' => 4, 'generic' => 2, 'graph' => 1, 'descr' => 'Denied'],
        ['value' => 5, 'generic' => 3, 'graph' => 1, 'descr' => 'Unknown'],
        ['value' => 6, 'generic' => 1, 'graph' => 1, 'descr' => 'Registered, roaming'],
        ['value' => 7, 'generic' => 2, 'graph' => 1, 'descr' => 'Not supported'],
        ['value' => 8, 'generic' => 1, 'graph' => 1, 'descr' => 'Registered, emergency services only'],
    ];

    $networkType = [
        ['value' => 1, 'generic' => 0, 'graph' => 1, 'descr' => 'Auto'],
        ['value' => 2, 'generic' => 1, 'graph' => 1, 'descr' => 'No service'],
        ['value' => 3, 'generic' => 0, 'graph' => 1, 'descr' => '2G'],
        ['value' => 4, 'generic' => 0, 'graph' => 1, 'descr' => 'GSM'],
        ['value' => 5, 'generic' => 0, 'graph' => 1, 'descr' => 'GPRS'],
        ['value' => 6, 'generic' => 0, 'graph' => 1, 'descr' => 'EDGE'],
        ['value' => 7, 'generic' => 0, 'graph' => 1, 'descr' => '3G'],
        ['value' => 8, 'generic' => 0, 'graph' => 1, 'descr' => 'WCDMA'],
        ['value' => 9, 'generic' => 0, 'graph' => 1, 'descr' => 'TDSCDMA'],
        ['value' => 10, 'generic' => 0, 'graph' => 1, 'descr' => 'CDMA'],
        ['value' => 11, 'generic' => 0, 'graph' => 1, 'descr' => 'EVDO'],
        ['value' => 12, 'generic' => 0, 'graph' => 1, 'descr' => 'CDMA-EVDO'],
        ['value' => 13, 'generic' => 0, 'graph' => 1, 'descr' => 'HSDPA'],
        ['value' => 14, 'generic' => 0, 'graph' => 1, 'descr' => 'HSUPA'],
        ['value' => 15, 'generic' => 0, 'graph' => 1, 'descr' => 'HSPA+'],
        ['value' => 16, 'generic' => 0, 'graph' => 1, 'descr' => 'EHRPD'],
        ['value' => 17, 'generic' => 0, 'graph' => 1, 'descr' => 'HDR'],
        ['value' => 18, 'generic' => 0, 'graph' => 1, 'descr' => 'UMTS'],
        ['value' => 19, 'generic' => 0, 'graph' => 1, 'descr' => 'HSDPA+HSUPA'],
        ['value' => 20, 'generic' => 0, 'graph' => 1, 'descr' => '4G'],
        ['value' => 21, 'generic' => 0, 'graph' => 1, 'descr' => 'LTE'],
        ['value' => 22, 'generic' => 0, 'graph' => 1, 'descr' => '5G'],
        ['value' => 23, 'generic' => 0, 'graph' => 1, 'descr' => 'NR5G'],
        ['value' => 24, 'generic' => 0, 'graph' => 1, 'descr' => '5G-NSA'],
        ['value' => 25, 'generic' => 0, 'graph' => 1, 'descr' => '5G-SA'],
        ['value' => 26, 'generic' => 0, 'graph' => 1, 'descr' => 'CAT-M1'],
        ['value' => 27, 'generic' => 0, 'graph' => 1, 'descr' => 'CAT-NB'],
        ['value' => 28, 'generic' => 0, 'graph' => 1, 'descr' => '2G_3G'],
        ['value' => 29, 'generic' => 0, 'graph' => 1, 'descr' => 'GSM_WCDMA'],
        ['value' => 30, 'generic' => 0, 'graph' => 1, 'descr' => '2G_4G'],
        ['value' => 31, 'generic' => 0, 'graph' => 1, 'descr' => 'GSM_LTE'],
        ['value' => 32, 'generic' => 0, 'graph' => 1, 'descr' => '3G_4G'],
        ['value' => 33, 'generic' => 0, 'graph' => 1, 'descr' => 'WCDMA_LTE'],
        ['value' => 34, 'generic' => 0, 'graph' => 1, 'descr' => '3G_5G'],
        ['value' => 35, 'generic' => 0, 'graph' => 1, 'descr' => 'WCDMA_NR5G'],
        ['value' => 36, 'generic' => 0, 'graph' => 1, 'descr' => '4G_5G'],
        ['value' => 37, 'generic' => 0, 'graph' => 1, 'descr' => 'LTE_NR5G'],
        ['value' => 38, 'generic' => 3, 'graph' => 1, 'descr' => 'Unknown'],
    ];

    $state_name = 'mSimState';
    $connection_state_name = 'mConnectionState';
    $pin_state_name = 'mPinState';
    $net_state_name = 'mNetState';
    $net_type_name = 'mNetworkType';

    create_state_index($state_name, $simState);
    create_state_index($connection_state_name, $connectionState);
    create_state_index($pin_state_name, $pinState);
    create_state_index($net_state_name, $netState);
    create_state_index($net_type_name, $networkType);

    foreach ($oidsSimState as $index => $entry) {
        $entry['mSimState'] = $simStateMapping[$entry['mSimState']] ?? 0;

        discover_sensor(
            $valid['sensor'],
            'state',
            $device,
            ".1.3.6.1.4.1.48690.2.2.1.9.$index",
            1,
            $state_name,
            'SIM State',
            '1',
            '1',
            null,
            null,
            null,
            null,
            $entry['mSimState']
        );

        create_sensor_to_state_index($device, $state_name, 1);
    }

    foreach ($oidsConnectionState as $index => $entry) {
        $entry['mConnectionState'] = $connectionStateMapping[$entry['mConnectionState']] ?? 0;

        discover_sensor(
            $valid['sensor'],
            'state',
            $device,
            ".1.3.6.1.4.1.48690.2.2.1.15.$index",
            1,
            $connection_state_name,
            'Connection State',
            '1',
            '1',
            null,
            null,
            null,
            null,
            $entry['mConnectionState']
        );

        create_sensor_to_state_index($device, $connection_state_name, 1);
    }

    foreach ($oidsPinState as $index => $entry) {
        $entry['mPinState'] = $pinStateMapping[$entry['mPinState']] ?? 0;

        discover_sensor(
            $valid['sensor'],
            'state',
            $device,
            ".1.3.6.1.4.1.48690.2.2.1.10.$index",
            1,
            $pin_state_name,
            'PIN State',
            '1',
            '1',
            null,
            null,
            null,
            null,
            $entry['mPinState']
        );

        create_sensor_to_state_index($device, $pin_state_name, 1);
    }

    foreach ($oidsNetState as $index => $entry) {
        $entry['mNetState'] = $netStateMapping[$entry['mNetState']] ?? 0;

        discover_sensor(
            $valid['sensor'],
            'state',
            $device,
            ".1.3.6.1.4.1.48690.2.2.1.11.$index",
            1,
            $net_state_name,
            'Net State',
            '1',
            '1',
            null,
            null,
            null,
            null,
            $entry['mNetState']
        );

        create_sensor_to_state_index($device, $net_state_name, 1);
    }

    foreach ($oidsNetworkType as $index => $entry) {
        $entry['mNetworkType'] = $networkTypeMapping[$entry['mNetworkType']] ?? 0;

        discover_sensor(
            $valid['sensor'],
            'state',
            $device,
            ".1.3.6.1.4.1.48690.2.2.1.16.$index",
            1,
            $net_type_name,
            'Net Type',
            '1',
            '1',
            null,
            null,
            null,
            null,
            $entry['mNetworkType']
        );

        create_sensor_to_state_index($device, $net_type_name, 1);
    }

    unset($oidsSimState, $oidsConnectionState, $oidsPinState, $oidsNetState, $oidsNetworkType, $index, $entry);
}
