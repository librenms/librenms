<?php
/*
 * LibreNMS
 *
 * Copyright (c) 2016 Søren Friis Rosiak <sorenrosiak@gmail.com> 
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

if ($device['os_group'] == 'cisco') {
    $tables = array(
        array('ciscoEnvMonVoltageStatusTable','.1.3.6.1.4.1.9.9.13.1.2.1.7.','ciscoEnvMonVoltageState','ciscoEnvMonVoltageStatusDescr', 'CISCO-ENVMON-MIB') ,
        array('ciscoEnvMonTemperatureStatusTable','.1.3.6.1.4.1.9.9.13.1.3.1.6.','ciscoEnvMonTemperatureState','ciscoEnvMonTemperatureStatusDescr', 'CISCO-ENVMON-MIB') ,
        array('ciscoEnvMonFanStatusTable','.1.3.6.1.4.1.9.9.13.1.4.1.3.','ciscoEnvMonFanState','ciscoEnvMonFanStatusDescr', 'CISCO-ENVMON-MIB') ,
        array('ciscoEnvMonSupplyStatusTable','.1.3.6.1.4.1.9.9.13.1.5.1.3.','ciscoEnvMonSupplyState','ciscoEnvMonSupplyStatusDescr', 'CISCO-ENVMON-MIB') ,
        array('cRFCfgRedundancyOperMode','.1.3.6.1.4.1.9.9.176.1.2.14.','cRFCfgRedundancyOperMode','VSS Mode', 'CISCO-RF-MIB') ,
        array('cRFStatusUnitState','.1.3.6.1.4.1.9.9.176.1.1.2.','cRFStatusUnitState','VSS Device State', 'CISCO-RF-MIB') ,
        array('cRFStatusPeerUnitState','.1.3.6.1.4.1.9.9.176.1.1.4.','cRFStatusPeerUnitState','VSS Peer State', 'CISCO-RF-MIB')
    );

    foreach ($tables as $tablevalue) {
        $temp = snmpwalk_cache_multi_oid($device, $tablevalue[0], array(), $tablevalue[4]);
        $cur_oid = $tablevalue[1];

        if (is_array($temp)) {
            if ($temp[0][$tablevalue[2]] == 'nonRedundant') {
                break;
            }

            //Create State Index
            $state_name = $tablevalue[2];
            $state_index_id = create_state_index($state_name);

            //Create State Translation
            if ($state_index_id !== null) {
                if ($state_name == 'cRFStatusUnitState' || $state_name == 'cRFStatusPeerUnitState') {
                    $states = array(
                        array($state_index_id,'notKnown',0,1,1) ,
                        array($state_index_id,'disabled',0,2,0) ,
                        array($state_index_id,'initialization',0,3,1) ,
                        array($state_index_id,'negotiation',0,4,1) ,
                        array($state_index_id,'standbyCold',0,5,1) ,
                        array($state_index_id,'standbyColdConfig',0,6,1) ,
                        array($state_index_id,'standbyColdFileSys',0,7,1) ,
                        array($state_index_id,'standbyColdBulk',0,8,1) ,
                        array($state_index_id,'standbyHot',0,9,0) ,
                        array($state_index_id,'activeFast',0,10,1) ,
                        array($state_index_id,'activeDrain',0,11,1) ,
                        array($state_index_id,'activePreconfig',0,12,1) ,
                        array($state_index_id,'activePostconfig',0,13,1) ,
                        array($state_index_id,'active',0,14,0) ,
                        array($state_index_id,'activeExtraload',0,15,1) ,
                        array($state_index_id,'activeHandback',0,16,1)
                    );
                } elseif ($state_name == 'cRFCfgRedundancyOperMode') {
                    $states = array(
                        array($state_index_id,'nonRedundant',0,1,0) ,
                        array($state_index_id,'staticLoadShareNonRedundant',0,2,0) ,
                        array($state_index_id,'dynamicLoadShareNonRedundant',0,3,0) ,
                        array($state_index_id,'staticLoadShareRedundant',0,4,0) ,
                        array($state_index_id,'dynamicLoadShareRedundant',0,5,0) ,
                        array($state_index_id,'coldStandbyRedundant',0,6,0) ,
                        array($state_index_id,'warmStandbyRedundant',0,7,0) ,
                        array($state_index_id,'hotStandbyRedundant',0,8,0)
                    );
                } else {
                    $states = array(
                        array($state_index_id,'normal',0,1,0) ,
                        array($state_index_id,'warning',0,2,1) ,
                        array($state_index_id,'critical',0,3,2) ,
                        array($state_index_id,'shutdown',0,4,3) ,
                        array($state_index_id,'notPresent',0,5,3) ,
                        array($state_index_id,'notFunctioning',0,6,2)
                    );
                }

                foreach ($states as $value) {
                    $insert = array(
                        'state_index_id' => $value[0],
                        'state_descr' => $value[1],
                        'state_draw_graph' => $value[2],
                        'state_value' => $value[3],
                        'state_generic_value' => $value[4]
                    );
                    dbInsert($insert, 'state_translations');
                }
            }

            foreach ($temp as $index => $entry) {
                //Discover Sensors
                $descr = ucwords($temp[$index][$tablevalue[3]]);
                if ($state_name == 'cRFStatusUnitState' || $state_name == 'cRFStatusPeerUnitState' || $state_name == 'cRFCfgRedundancyOperMode') {
                    $descr = $tablevalue[3];
                }
                discover_sensor($valid['sensor'], 'state', $device, $cur_oid.$index, $index, $state_name, $descr, '1', '1', null, null, null, null, $temp[$index][$tablevalue[2]], 'snmp', $index);

                //Create Sensor To State Index
                create_sensor_to_state_index($device, $state_name, $index);
            }
        }
    }
}
