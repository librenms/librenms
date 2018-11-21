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

$role_data = snmpwalk_cache_oid($device, 'cswSwitchRole', array(), 'CISCO-STACKWISE-MIB');
$redundant_data = snmp_get($device, "cswRingRedundant.0", "-OQv", "CISCO-STACKWISE-MIB");

$tables = array(
    array('ciscoEnvMonVoltageStatusTable','.1.3.6.1.4.1.9.9.13.1.2.1.7.','ciscoEnvMonVoltageState','ciscoEnvMonVoltageStatusDescr', 'CISCO-ENVMON-MIB') ,
    array('ciscoEnvMonTemperatureStatusTable','.1.3.6.1.4.1.9.9.13.1.3.1.6.','ciscoEnvMonTemperatureState','ciscoEnvMonTemperatureStatusDescr', 'CISCO-ENVMON-MIB') ,
    array('ciscoEnvMonFanStatusTable','.1.3.6.1.4.1.9.9.13.1.4.1.3.','ciscoEnvMonFanState','ciscoEnvMonFanStatusDescr', 'CISCO-ENVMON-MIB') ,
    array('ciscoEnvMonSupplyStatusTable','.1.3.6.1.4.1.9.9.13.1.5.1.3.','ciscoEnvMonSupplyState','ciscoEnvMonSupplyStatusDescr', 'CISCO-ENVMON-MIB') ,
    array('cefcFRUPowerStatusTable','.1.3.6.1.4.1.9.9.117.1.1.2.1.2.','cefcFRUPowerOperStatus','Sensor Name', 'CISCO-ENTITY-FRU-CONTROL-MIB') ,
    array('cswGlobals','.1.3.6.1.4.1.9.9.500.1.1.3.','cswRingRedundant','Stack Ring - Redundant', 'CISCO-STACKWISE-MIB') ,
    array('cswSwitchRole','.1.3.6.1.4.1.9.9.500.1.2.1.1.3.','cswSwitchRole','Stack Role - Switch#', 'CISCO-STACKWISE-MIB') ,
    array('cswSwitchState','.1.3.6.1.4.1.9.9.500.1.2.1.1.6.','cswSwitchState','Stack State - Switch#', 'CISCO-STACKWISE-MIB') ,
    array('cswStackPortOperStatus','.1.3.6.1.4.1.9.9.500.1.2.2.1.1.','cswStackPortOperStatus','Stack Port Status - ', 'CISCO-STACKWISE-MIB') ,
    array('cRFCfgRedundancyOperMode','.1.3.6.1.4.1.9.9.176.1.2.14.','cRFCfgRedundancyOperMode','VSS Mode', 'CISCO-RF-MIB') ,
    array('cRFStatusUnitState','.1.3.6.1.4.1.9.9.176.1.1.2.','cRFStatusUnitState','VSS Device State', 'CISCO-RF-MIB') ,
    array('cRFStatusPeerUnitState','.1.3.6.1.4.1.9.9.176.1.1.4.','cRFStatusPeerUnitState','VSS Peer State', 'CISCO-RF-MIB')
);

foreach ($tables as $tablevalue) {
    //Some switches on 15.x expose this information regardless if they are stacked or not, we try to mitigate that by doing the following.
    if (($tablevalue[0] == 'cswGlobals' || $tablevalue[0] == 'cswSwitchRole' || $tablevalue[0] == 'cswSwitchState' || $tablevalue[0] == 'cswStackPortOperStatus') && $redundant_data == 'false' && count($role_data) == 1) {
        continue;
    }

    $temp = snmpwalk_cache_multi_oid($device, $tablevalue[0], array(), $tablevalue[4]);
    $cur_oid = $tablevalue[1];

    if (is_array($temp)) {
        if ($temp[0][$tablevalue[2]] == 'nonRedundant' || $temp[0]['cswMaxSwitchNum'] == '1') {
            break;
        }

        //Create State Index
        $state_name = $tablevalue[2];

        //Create State Translation
        if ($state_index_id !== null) {
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
                    ['value' => 16, 'generic' => 1, 'graph' => 0, 'descr' => 'activeHandback']
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
                    ['value' => 8, 'generic' => 0, 'graph' => 0, 'descr' => 'hotStandbyRedundant']
                ];
            } elseif ($state_name == 'cswRingRedundant') {
                $states = [
                    ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'true'],
                    ['value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'false']
                ];
            } elseif ($state_name == 'cswSwitchRole') {
                $states = [
                    ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'master'],
                    ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'member'],
                    ['value' => 3, 'generic' => 0, 'graph' => 0, 'descr' => 'notMember'],
                    ['value' => 4, 'generic' => 0, 'graph' => 0, 'descr' => 'standby']
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
                    ['value' => 11, 'generic' => 1, 'graph' => 0, 'descr' => 'removed']
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
                    ['value' => 12, 'generic' => 1, 'graph' => 0, 'descr' => 'on (no inline power)']
                ];
            } else {
                $states = [
                    ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'normal'],
                    ['value' => 2, 'generic' => 1, 'graph' => 0, 'descr' => 'warning'],
                    ['value' => 3, 'generic' => 2, 'graph' => 0, 'descr' => 'critical'],
                    ['value' => 4, 'generic' => 3, 'graph' => 0, 'descr' => 'shutdown'],
                    ['value' => 5, 'generic' => 3, 'graph' => 0, 'descr' => 'notPresent'],
                    ['value' => 6, 'generic' => 2, 'graph' => 0, 'descr' => 'notFunctioning']
                ];
            }

            create_state_index($state_name, $states);
        }

        foreach ($temp as $index => $entry) {
            if ($tablevalue[2] == 'ciscoEnvMonTemperatureState' && (empty($temp[$index][$tablevalue[3]]))) {
                d_echo('Invalid sensor, skipping..');
            } else {
                //Discover Sensors
                $descr = ucwords($temp[$index][$tablevalue[3]]);
                if ($state_name == 'cRFStatusUnitState' || $state_name == 'cRFStatusPeerUnitState' || $state_name == 'cRFCfgRedundancyOperMode' || $state_name == 'cswRingRedundant') {
                    $descr = $tablevalue[3];
                } elseif ($state_name == 'cswSwitchRole') {
                    $swrolenumber++;
                    $descr = $tablevalue[3] . $swrolenumber;
                } elseif ($state_name == 'cswSwitchState') {
                    $swstatenumber++;
                    $descr = $tablevalue[3] . $swstatenumber;
                } elseif ($state_name == 'cswStackPortOperStatus') {
                    $stack_port_descr = get_port_by_index_cache($device['device_id'], $index);
                    $descr = $tablevalue[3] . $stack_port_descr['ifDescr'];
                } elseif ($state_name == 'cefcFRUPowerOperStatus') {
                    $descr = snmp_get($device, 'entPhysicalName.'.$index, '-Oqv', 'ENTITY-MIB');
                }
                discover_sensor($valid['sensor'], 'state', $device, $cur_oid.$index, $index, $state_name, $descr, '1', '1', null, null, null, null, $temp[$index][$tablevalue[2]], 'snmp', $index);

                //Create Sensor To State Index
                create_sensor_to_state_index($device, $state_name, $index);
            }
        }
    }
}

unset($role_data, $redundant_data);
