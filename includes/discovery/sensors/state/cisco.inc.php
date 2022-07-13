<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2018 Søren Friis Rosiak <sorenrosiak@gmail.com>
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$role_data = snmpwalk_cache_oid($device, 'cswSwitchRole', [], 'CISCO-STACKWISE-MIB');
$redundant_data = snmp_get($device, 'cswRingRedundant.0', '-OQv', 'CISCO-STACKWISE-MIB');

$tables = [
    ['num_oid' => '.1.3.6.1.4.1.9.9.661.1.3.1.1.6.',    'oid' => 'c3gModemStatus',                       'state_name' => 'c3gModemStatus',               'mib' => 'CISCO-WAN-3G-MIB',                'descr' => 'Modem status'],
    ['num_oid' => '.1.3.6.1.4.1.9.9.661.1.3.4.1.1.3.',  'oid' => 'c3gGsmCurrentBand',                    'state_name' => 'c3gGsmCurrentBand',            'mib' => 'CISCO-WAN-3G-MIB',                'descr' => 'Current band'],
    ['num_oid' => '.1.3.6.1.4.1.9.9.661.1.3.2.1.5.',    'oid' => 'c3gGsmPacketService',                  'state_name' => 'c3gGsmPacketService',          'mib' => 'CISCO-WAN-3G-MIB',                'descr' => 'Packet service'],
    ['num_oid' => '.1.3.6.1.4.1.9.9.661.1.3.2.1.6.',    'oid' => 'c3gGsmCurrentRoamingStatus',           'state_name' => 'c3gGsmCurrentRoamingStatus',   'mib' => 'CISCO-WAN-3G-MIB',                'descr' => 'Roaming status'],
    ['num_oid' => '.1.3.6.1.4.1.9.9.661.1.3.5.1.1.2.',  'oid' => 'c3gGsmSimStatus',                      'state_name' => 'c3gGsmSimStatus',              'mib' => 'CISCO-WAN-3G-MIB',                'descr' => 'SIM status'],
    ['num_oid' => '.1.3.6.1.4.1.9.9.13.1.2.1.7.',       'oid' => 'ciscoEnvMonVoltageStatusTable',        'state_name' => 'ciscoEnvMonVoltageState',      'mib' => 'CISCO-ENVMON-MIB',                'descr' => 'ciscoEnvMonVoltageStatusDescr'],
    ['num_oid' => '.1.3.6.1.4.1.9.9.13.1.3.1.6.',       'oid' => 'ciscoEnvMonTemperatureStatusTable',    'state_name' => 'ciscoEnvMonTemperatureState',  'mib' => 'CISCO-ENVMON-MIB',                'descr' => 'ciscoEnvMonTemperatureStatusDescr'],
    ['num_oid' => '.1.3.6.1.4.1.9.9.13.1.4.1.3.',       'oid' => 'ciscoEnvMonFanStatusTable',            'state_name' => 'ciscoEnvMonFanState',          'mib' => 'CISCO-ENVMON-MIB',                'descr' => 'ciscoEnvMonFanStatusDescr'],
    ['num_oid' => '.1.3.6.1.4.1.9.9.13.1.5.1.3.',       'oid' => 'ciscoEnvMonSupplyStatusTable',         'state_name' => 'ciscoEnvMonSupplyState',       'mib' => 'CISCO-ENVMON-MIB',                'descr' => 'ciscoEnvMonSupplyStatusDescr'],
    ['num_oid' => '.1.3.6.1.4.1.9.9.117.1.1.2.1.2.',    'oid' => 'cefcFRUPowerStatusTable',              'state_name' => 'cefcFRUPowerOperStatus',       'mib' => 'CISCO-ENTITY-FRU-CONTROL-MIB',    'descr' => 'Sensor Name'],
    ['num_oid' => '.1.3.6.1.4.1.9.9.176.1.1.2.',        'oid' => 'cRFStatusUnitState',                   'state_name' => 'cRFStatusUnitState',           'mib' => 'CISCO-RF-MIB',                    'descr' => 'VSS Device State'],
    ['num_oid' => '.1.3.6.1.4.1.9.9.176.1.1.4.',        'oid' => 'cRFStatusPeerUnitState',               'state_name' => 'cRFStatusPeerUnitState',       'mib' => 'CISCO-RF-MIB',                    'descr' => 'VSS Peer State'],
    ['num_oid' => '.1.3.6.1.4.1.9.9.176.1.2.14.',       'oid' => 'cRFCfgRedundancyOperMode',             'state_name' => 'cRFCfgRedundancyOperMode',     'mib' => 'CISCO-RF-MIB',                    'descr' => 'VSS Mode'],
    ['num_oid' => '.1.3.6.1.4.1.9.9.500.1.1.3.',        'oid' => 'cswGlobals',                           'state_name' => 'cswRingRedundant',             'mib' => 'CISCO-STACKWISE-MIB',             'descr' => 'Stack Ring - Redundant'],
    ['num_oid' => '.1.3.6.1.4.1.9.9.500.1.2.1.1.3.',    'oid' => 'cswSwitchRole',                        'state_name' => 'cswSwitchRole',                'mib' => 'CISCO-STACKWISE-MIB',             'descr' => 'Stack Role - Switch#'],
    ['num_oid' => '.1.3.6.1.4.1.9.9.500.1.2.1.1.6.',    'oid' => 'cswSwitchState',                       'state_name' => 'cswSwitchState',               'mib' => 'CISCO-STACKWISE-MIB',             'descr' => 'Stack State - Switch#'],
    ['num_oid' => '.1.3.6.1.4.1.9.9.500.1.2.2.1.1.',    'oid' => 'cswStackPortOperStatus',               'state_name' => 'cswStackPortOperStatus',       'mib' => 'CISCO-STACKWISE-MIB',             'descr' => 'Stack Port Status - '],
];

foreach ($tables as $tablevalue) {
    //Some switches on 15.x expose this information regardless if they are stacked or not, we try to mitigate that by doing the following.
    if (($tablevalue['oid'] == 'cswGlobals' || $tablevalue['oid'] == 'cswSwitchRole' || $tablevalue['oid'] == 'cswSwitchState' || $tablevalue['oid'] == 'cswStackPortOperStatus') && $redundant_data == 'false' && count($role_data) == 1) {
        continue;
    }

    $temp = snmpwalk_cache_multi_oid($device, $tablevalue['oid'], [], $tablevalue['mib']);
    $cur_oid = $tablevalue['num_oid'];

    if (is_array($temp)) {
        if ($temp[0][$tablevalue['state_name']] == 'nonRedundant' || $temp[0]['cswMaxSwitchNum'] == '1') {
            break;
        }

        //Create State Index
        $state_name = $tablevalue['state_name'];

        //Create State Translation
        if ($state_name == 'cRFStatusUnitState' || $state_name == 'cRFStatusPeerUnitState') {
            $states = [
                ['value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'notKnown'],
                ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'disabled'],
                ['value' => 3, 'generic' => 1, 'graph' => 0, 'descr' => 'initialization'],
                ['value' => 4, 'generic' => 1, 'graph' => 0, 'descr' => 'negotiation'],
                ['value' => 5, 'generic' => 1, 'graph' => 0, 'descr' => 'standbyCold'],
                ['value' => 6, 'generic' => 1, 'graph' => 0, 'descr' => 'standbyColdConfig'],
                ['value' => 7, 'generic' => 1, 'graph' => 0, 'descr' => 'standbyColdFileSys'],
                ['value' => 8, 'generic' => 1, 'graph' => 0, 'descr' => 'standbyColdBulk'],
                ['value' => 9, 'generic' => 0, 'graph' => 0, 'descr' => 'standbyHot'],
                ['value' => 10, 'generic' => 1, 'graph' => 0, 'descr' => 'activeFast'],
                ['value' => 11, 'generic' => 1, 'graph' => 0, 'descr' => 'activeDrain'],
                ['value' => 12, 'generic' => 1, 'graph' => 0, 'descr' => 'activePreconfig'],
                ['value' => 13, 'generic' => 1, 'graph' => 0, 'descr' => 'activePostconfig'],
                ['value' => 14, 'generic' => 0, 'graph' => 0, 'descr' => 'active'],
                ['value' => 15, 'generic' => 1, 'graph' => 0, 'descr' => 'activeExtraload'],
                ['value' => 16, 'generic' => 1, 'graph' => 0, 'descr' => 'activeHandback'],
            ];
        } elseif ($state_name == 'cRFCfgRedundancyOperMode') {
            $states = [
                ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'nonRedundant'],
                ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'staticLoadShareNonRedundant'],
                ['value' => 3, 'generic' => 0, 'graph' => 0, 'descr' => 'dynamicLoadShareNonRedundant'],
                ['value' => 4, 'generic' => 0, 'graph' => 0, 'descr' => 'staticLoadShareRedundant'],
                ['value' => 5, 'generic' => 0, 'graph' => 0, 'descr' => 'dynamicLoadShareRedundant'],
                ['value' => 6, 'generic' => 0, 'graph' => 0, 'descr' => 'coldStandbyRedundant'],
                ['value' => 7, 'generic' => 0, 'graph' => 0, 'descr' => 'warmStandbyRedundant'],
                ['value' => 8, 'generic' => 0, 'graph' => 0, 'descr' => 'hotStandbyRedundant'],
            ];
        } elseif ($state_name == 'cswRingRedundant') {
            $states = [
                ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'true'],
                ['value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'false'],
            ];
        } elseif ($state_name == 'cswSwitchRole') {
            $states = [
                ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'master'],
                ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'member'],
                ['value' => 3, 'generic' => 0, 'graph' => 0, 'descr' => 'notMember'],
                ['value' => 4, 'generic' => 0, 'graph' => 0, 'descr' => 'standby'],
            ];
        } elseif ($state_name == 'cswSwitchState') {
            $states = [
                ['value' => 1, 'generic' => 1, 'graph' => 0, 'descr' => 'waiting'],
                ['value' => 2, 'generic' => 1, 'graph' => 0, 'descr' => 'progressing'],
                ['value' => 3, 'generic' => 1, 'graph' => 0, 'descr' => 'added'],
                ['value' => 4, 'generic' => 0, 'graph' => 0, 'descr' => 'ready'],
                ['value' => 5, 'generic' => 2, 'graph' => 0, 'descr' => 'sdmMismatch'],
                ['value' => 6, 'generic' => 2, 'graph' => 0, 'descr' => 'verMismatch'],
                ['value' => 7, 'generic' => 2, 'graph' => 0, 'descr' => 'featureMismatch'],
                ['value' => 8, 'generic' => 2, 'graph' => 0, 'descr' => 'newMasterInit'],
                ['value' => 9, 'generic' => 1, 'graph' => 0, 'descr' => 'provisioned'],
                ['value' => 10, 'generic' => 2, 'graph' => 0, 'descr' => 'invalid'],
                ['value' => 11, 'generic' => 1, 'graph' => 0, 'descr' => 'removed'],
            ];
        } elseif ($state_name == 'cefcFRUPowerOperStatus') {
            $states = [
                ['value' => 1, 'generic' => 2, 'graph' => 0, 'descr' => 'off (other)'],
                ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'on'],
                ['value' => 3, 'generic' => 1, 'graph' => 0, 'descr' => 'off (admin)'],
                ['value' => 4, 'generic' => 2, 'graph' => 0, 'descr' => 'off (denied)'],
                ['value' => 5, 'generic' => 2, 'graph' => 0, 'descr' => 'off (environmental)'],
                ['value' => 6, 'generic' => 2, 'graph' => 0, 'descr' => 'off (temperature)'],
                ['value' => 7, 'generic' => 2, 'graph' => 0, 'descr' => 'off (fan)'],
                ['value' => 8, 'generic' => 2, 'graph' => 0, 'descr' => 'failed'],
                ['value' => 9, 'generic' => 1, 'graph' => 0, 'descr' => 'on (fan failed)'],
                ['value' => 10, 'generic' => 2, 'graph' => 0, 'descr' => 'off (cooling)'],
                ['value' => 11, 'generic' => 2, 'graph' => 0, 'descr' => 'off (connector rating)'],
                ['value' => 12, 'generic' => 1, 'graph' => 0, 'descr' => 'on (no inline power)'],
            ];
        } elseif ($state_name == 'c3gModemStatus') {
            $states = [
                ['value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'unknown'],
                ['value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'offline'],
                ['value' => 3, 'generic' => 0, 'graph' => 0, 'descr' => 'online'],
                ['value' => 4, 'generic' => 1, 'graph' => 0, 'descr' => 'low power mode'],
            ];
        } elseif ($state_name == 'c3gGsmCurrentBand') {
            $states = [
                ['value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'unknown'],
                ['value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'invalid'],
                ['value' => 3, 'generic' => 3, 'graph' => 0, 'descr' => 'none'],
                ['value' => 4, 'generic' => 0, 'graph' => 0, 'descr' => 'gsm850'],
                ['value' => 5, 'generic' => 0, 'graph' => 0, 'descr' => 'gsm900'],
                ['value' => 6, 'generic' => 0, 'graph' => 0, 'descr' => 'gsm1800'],
                ['value' => 7, 'generic' => 0, 'graph' => 0, 'descr' => 'gsm1900'],
                ['value' => 8, 'generic' => 0, 'graph' => 0, 'descr' => 'wcdma800'],
                ['value' => 9, 'generic' => 0, 'graph' => 0, 'descr' => 'wcdma850'],
                ['value' => 10, 'generic' => 0, 'graph' => 0, 'descr' => 'wcdma1900'],
                ['value' => 11, 'generic' => 0, 'graph' => 0, 'descr' => 'wcdma2100'],
                ['value' => 12, 'generic' => 0, 'graph' => 0, 'descr' => 'lte band'],
            ];
        } elseif ($state_name == 'c3gGsmPacketService') {
            $states = [
                ['value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'unknown'],
                ['value' => 2, 'generic' => 1, 'graph' => 0, 'descr' => 'none'],
                ['value' => 3, 'generic' => 0, 'graph' => 0, 'descr' => 'gprs'],
                ['value' => 4, 'generic' => 0, 'graph' => 0, 'descr' => 'edge'],
                ['value' => 5, 'generic' => 0, 'graph' => 0, 'descr' => 'umts wcdma'],
                ['value' => 6, 'generic' => 0, 'graph' => 0, 'descr' => 'hsdpa'],
                ['value' => 7, 'generic' => 0, 'graph' => 0, 'descr' => 'hsupa'],
                ['value' => 8, 'generic' => 0, 'graph' => 0, 'descr' => 'hspa'],
                ['value' => 9, 'generic' => 0, 'graph' => 0, 'descr' => 'hspa plus'],
                ['value' => 10, 'generic' => 0, 'graph' => 0, 'descr' => 'lte'],
            ];
        } elseif ($state_name == 'c3gGsmCurrentRoamingStatus') {
            $states = [
                ['value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'unknown'],
                ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'roaming'],
                ['value' => 3, 'generic' => 0, 'graph' => 0, 'descr' => 'home'],
            ];
        } elseif ($state_name == 'c3gGsmSimStatus') {
            $states = [
                ['value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'unknown'],
                ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'ok'],
                ['value' => 3, 'generic' => 0, 'graph' => 0, 'descr' => 'not inserted'],
                ['value' => 4, 'generic' => 1, 'graph' => 0, 'descr' => 'removed'],
                ['value' => 5, 'generic' => 2, 'graph' => 0, 'descr' => 'initFailure'],
                ['value' => 6, 'generic' => 2, 'graph' => 0, 'descr' => 'generalFailure'],
                ['value' => 7, 'generic' => 2, 'graph' => 0, 'descr' => 'locked'],
                ['value' => 8, 'generic' => 2, 'graph' => 0, 'descr' => 'chv1Blocked'],
                ['value' => 9, 'generic' => 2, 'graph' => 0, 'descr' => 'chv2Blocked'],
                ['value' => 10, 'generic' => 2, 'graph' => 0, 'descr' => 'chv1Rejected'],
                ['value' => 11, 'generic' => 2, 'graph' => 0, 'descr' => 'wcchv2Rejecteddma2100'],
                ['value' => 12, 'generic' => 2, 'graph' => 0, 'descr' => 'mepLocked'],
                ['value' => 13, 'generic' => 2, 'graph' => 0, 'descr' => 'networkRejected'],
            ];
        } else {
            $states = [
                ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'normal'],
                ['value' => 2, 'generic' => 1, 'graph' => 0, 'descr' => 'warning'],
                ['value' => 3, 'generic' => 2, 'graph' => 0, 'descr' => 'critical'],
                ['value' => 4, 'generic' => 3, 'graph' => 0, 'descr' => 'shutdown'],
                ['value' => 5, 'generic' => 3, 'graph' => 0, 'descr' => 'notPresent'],
                ['value' => 6, 'generic' => 2, 'graph' => 0, 'descr' => 'notFunctioning'],
            ];
        }
        create_state_index($state_name, $states);

        foreach ($temp as $index => $entry) {
            $state_group = null;
            if ($tablevalue['state_name'] == 'ciscoEnvMonTemperatureState' && (empty($temp[$index][$tablevalue['descr']]))) {
                d_echo('Invalid sensor, skipping..');
            } else {
                //Discover Sensors
                $descr = ucwords($temp[$index][$tablevalue['descr']]);
                if ($state_name == 'cRFStatusUnitState' || $state_name == 'cRFStatusPeerUnitState' || $state_name == 'cRFCfgRedundancyOperMode' || $state_name == 'cswRingRedundant') {
                    $descr = $tablevalue['descr'];
                } elseif ($state_name == 'cswSwitchRole') {
                    $swrolenumber++;
                    $descr = $tablevalue['descr'] . $swrolenumber;
                } elseif ($state_name == 'cswSwitchState') {
                    $swstatenumber++;
                    $descr = $tablevalue['descr'] . $swstatenumber;
                } elseif ($state_name == 'cswStackPortOperStatus') {
                    $stack_port_descr = get_port_by_index_cache($device['device_id'], $index);
                    $descr = $tablevalue['descr'] . $stack_port_descr['ifDescr'];
                } elseif ($state_name == 'cefcFRUPowerOperStatus') {
                    $descr = snmp_get($device, 'entPhysicalName.' . $index, '-Oqv', 'ENTITY-MIB');
                } elseif ($state_name == 'c3gModemStatus' || $state_name == 'c3gGsmCurrentBand' || $state_name == 'c3gGsmPacketService' || $state_name == 'c3gGsmCurrentRoamingStatus' || $state_name == 'c3gGsmSimStatus') {
                    $descr = $tablevalue['descr'];
                    $state_group = snmp_get($device, 'entPhysicalName.' . $index, '-Oqv', 'ENTITY-MIB');
                }
                discover_sensor($valid['sensor'], 'state', $device, $cur_oid . $index, $index, $state_name, $descr, 1, 1, null, null, null, null, $temp[$index][$tablevalue['state_name']], 'snmp', $index, null, null, $state_group);

                //Create Sensor To State Index
                create_sensor_to_state_index($device, $state_name, $index);
            }
        }
    }
}

unset($role_data, $redundant_data);
