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

use Illuminate\Support\Facades\Log;

$role_data = SnmpQuery::walk('CISCO-STACKWISE-MIB::cswSwitchRole')->values();
$redundant_data = SnmpQuery::enumStrings()->get('CISCO-STACKWISE-MIB::cswRingRedundant.0')->value();
$entPhysName = SnmpQuery::get('ENTITY-MIB::entPhysicalName.1')->value();

$tables = [
    ['num_oid' => '.1.3.6.1.4.1.9.9.661.1.3.1.1.6.',    'oid' => 'CISCO-WAN-3G-MIB::c3gModemStatus',                            'state_name' => 'c3gModemStatus',                  'descr' => 'Modem status'],
    ['num_oid' => '.1.3.6.1.4.1.9.9.661.1.3.4.1.1.3.',  'oid' => 'CISCO-WAN-3G-MIB::c3gGsmCurrentBand',                         'state_name' => 'c3gGsmCurrentBand',               'descr' => 'Current band'],
    ['num_oid' => '.1.3.6.1.4.1.9.9.661.1.3.2.1.5.',    'oid' => 'CISCO-WAN-3G-MIB::c3gGsmPacketService',                       'state_name' => 'c3gGsmPacketService',             'descr' => 'Packet service'],
    ['num_oid' => '.1.3.6.1.4.1.9.9.661.1.3.2.1.6.',    'oid' => 'CISCO-WAN-3G-MIB::c3gGsmCurrentRoamingStatus',                'state_name' => 'c3gGsmCurrentRoamingStatus',      'descr' => 'Roaming status'],
    ['num_oid' => '.1.3.6.1.4.1.9.9.661.1.3.5.1.1.2.',  'oid' => 'CISCO-WAN-3G-MIB::c3gGsmSimStatus',                           'state_name' => 'c3gGsmSimStatus',                 'descr' => 'SIM status'],
    ['num_oid' => '.1.3.6.1.4.1.9.9.13.1.2.1.7.',       'oid' => 'CISCO-ENVMON-MIB::ciscoEnvMonVoltageStatusTable',             'state_name' => 'ciscoEnvMonVoltageState',         'descr' => 'ciscoEnvMonVoltageStatusDescr'],
    ['num_oid' => '.1.3.6.1.4.1.9.9.13.1.3.1.6.',       'oid' => 'CISCO-ENVMON-MIB::ciscoEnvMonTemperatureStatusTable',         'state_name' => 'ciscoEnvMonTemperatureState',     'descr' => 'ciscoEnvMonTemperatureStatusDescr'],
    ['num_oid' => '.1.3.6.1.4.1.9.9.13.1.4.1.3.',       'oid' => 'CISCO-ENVMON-MIB::ciscoEnvMonFanStatusTable',                 'state_name' => 'ciscoEnvMonFanState',             'descr' => 'ciscoEnvMonFanStatusDescr'],
    ['num_oid' => '.1.3.6.1.4.1.9.9.13.1.5.1.3.',       'oid' => 'CISCO-ENVMON-MIB::ciscoEnvMonSupplyStatusTable',              'state_name' => 'ciscoEnvMonSupplyState',          'descr' => 'ciscoEnvMonSupplyStatusDescr'],
    ['num_oid' => '.1.3.6.1.4.1.9.9.117.1.1.2.1.2.',    'oid' => 'CISCO-ENTITY-FRU-CONTROL-MIB::cefcFRUPowerStatusTable',       'state_name' => 'cefcFRUPowerOperStatus',          'descr' => 'Sensor Name'],
    ['num_oid' => '.1.3.6.1.4.1.9.9.176.1.1.2.',        'oid' => 'CISCO-RF-MIB::cRFStatusUnitState',                            'state_name' => 'cRFStatusUnitState',              'descr' => 'VSS Device State'],
    ['num_oid' => '.1.3.6.1.4.1.9.9.176.1.1.4.',        'oid' => 'CISCO-RF-MIB::cRFStatusPeerUnitState',                        'state_name' => 'cRFStatusPeerUnitState',          'descr' => 'VSS Peer State'],
    ['num_oid' => '.1.3.6.1.4.1.9.9.176.1.2.14.',       'oid' => 'CISCO-RF-MIB::cRFCfgRedundancyOperMode',                      'state_name' => 'cRFCfgRedundancyOperMode',        'descr' => 'VSS Mode'],
    ['num_oid' => '.1.3.6.1.4.1.9.9.500.1.1.3.',        'oid' => 'CISCO-STACKWISE-MIB::cswGlobals',                             'state_name' => 'cswRingRedundant',                'descr' => 'Stack Ring - Redundant'],
    ['num_oid' => '.1.3.6.1.4.1.9.9.500.1.2.1.1.3.',    'oid' => 'CISCO-STACKWISE-MIB::cswSwitchRole',                          'state_name' => 'cswSwitchRole',                   'descr' => 'Stack Role - Switch#'],
    ['num_oid' => '.1.3.6.1.4.1.9.9.500.1.2.1.1.6.',    'oid' => 'CISCO-STACKWISE-MIB::cswSwitchState',                         'state_name' => 'cswSwitchState',                  'descr' => 'Stack State - Switch#'],
    ['num_oid' => '.1.3.6.1.4.1.9.9.500.1.2.2.1.1.',    'oid' => 'CISCO-STACKWISE-MIB::cswStackPortOperStatus',                 'state_name' => 'cswStackPortOperStatus',          'descr' => 'Stack Port Status - '],
    ['num_oid' => '.1.3.6.1.4.1.9.9.601.1.3.1.1.4.',    'oid' => 'CISCO-RESILIENT-ETHERNET-PROTOCOL-MIB::crepSegmentComplete',  'state_name' => 'crepSegmentComplete',             'descr' => 'REP State - Segment '],
];

$swrolenumber = 0;
$swstatenumber = 0;
$repsegmentnumber = 0;

foreach ($tables as $tablevalue) {
    //Some switches on 15.x expose this information regardless if they are stacked or not, we try to mitigate that by doing the following.
    if (in_array($tablevalue['oid'], ['CISCO-STACKWISE-MIB::cswGlobals', 'CISCO-STACKWISE-MIB::cswSwitchRole', 'CISCO-STACKWISE-MIB::cswSwitchState', 'CISCO-STACKWISE-MIB::cswStackPortOperStatus']) && $redundant_data == 'false' && count($role_data) <= 1) {
        continue;
    }

    $temp = SnmpQuery::hideMib()->walk($tablevalue['oid'])->valuesByIndex();
    $cur_oid = $tablevalue['num_oid'];

    if (! empty($temp)) {
        $state_name = $tablevalue['state_name'];

        if ((isset($temp[0][$state_name]) && $temp[0][$state_name] == 'nonRedundant') || (isset($temp[0]['cswMaxSwitchNum']) && $temp[0]['cswMaxSwitchNum'] == '1')) {
            break;
        }
        // Cisco StackWise Virtual always reports FALSE (2) for cswRingRedundant OID
        // This OID has no meaning in the context of StackWise Virtual
        // Skip the creation of the "Stack Ring - Redundant" state sensor if the device operates in StackWise Virtual mode
        // This can be identified by "Virtual Stack" in entPhysicalName OID
        if (isset($temp[0]['cswRingRedundant']) && $temp[0]['cswRingRedundant'] == 2 && $entPhysName == 'Virtual Stack') {
            continue;
        }

        //Create State Index
        //Create State Translation
        $states = match ($state_name) {
            'cRFStatusUnitState','cRFStatusPeerUnitState' => [
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
            ],
            'cRFCfgRedundancyOperMode' => [
                ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'nonRedundant'],
                ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'staticLoadShareNonRedundant'],
                ['value' => 3, 'generic' => 0, 'graph' => 0, 'descr' => 'dynamicLoadShareNonRedundant'],
                ['value' => 4, 'generic' => 0, 'graph' => 0, 'descr' => 'staticLoadShareRedundant'],
                ['value' => 5, 'generic' => 0, 'graph' => 0, 'descr' => 'dynamicLoadShareRedundant'],
                ['value' => 6, 'generic' => 0, 'graph' => 0, 'descr' => 'coldStandbyRedundant'],
                ['value' => 7, 'generic' => 0, 'graph' => 0, 'descr' => 'warmStandbyRedundant'],
                ['value' => 8, 'generic' => 0, 'graph' => 0, 'descr' => 'hotStandbyRedundant'],
            ],
            'cswRingRedundant' => [
                ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'true'],
                ['value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'false'],
            ],
            'cswSwitchRole' => [
                ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'master'],
                ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'member'],
                ['value' => 3, 'generic' => 0, 'graph' => 0, 'descr' => 'notMember'],
                ['value' => 4, 'generic' => 0, 'graph' => 0, 'descr' => 'standby'],
            ],
            'cswSwitchState' => [
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
            ],
            'cefcFRUPowerOperStatus' => [
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
            ],
            'c3gModemStatus' => [
                ['value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'unknown'],
                ['value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'offline'],
                ['value' => 3, 'generic' => 0, 'graph' => 0, 'descr' => 'online'],
                ['value' => 4, 'generic' => 1, 'graph' => 0, 'descr' => 'low power mode'],
            ],
            'c3gGsmCurrentBand' => [
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
            ],
            'c3gGsmPacketService' => [
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
            ],
            'c3gGsmCurrentRoamingStatus' => [
                ['value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'unknown'],
                ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'roaming'],
                ['value' => 3, 'generic' => 0, 'graph' => 0, 'descr' => 'home'],
            ],
            'c3gGsmSimStatus' => [
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
            ],
            'crepSegmentComplete' => [
                ['value' => 0, 'generic' => 3, 'graph' => 0, 'descr' => 'unknown'],
                ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'complete'],
                ['value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'incomplete'],
            ],
            default => [
                ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'normal'],
                ['value' => 2, 'generic' => 1, 'graph' => 0, 'descr' => 'warning'],
                ['value' => 3, 'generic' => 2, 'graph' => 0, 'descr' => 'critical'],
                ['value' => 4, 'generic' => 3, 'graph' => 0, 'descr' => 'shutdown'],
                ['value' => 5, 'generic' => 3, 'graph' => 0, 'descr' => 'notPresent'],
                ['value' => 6, 'generic' => 2, 'graph' => 0, 'descr' => 'notFunctioning'],
            ],
        };
        create_state_index($state_name, $states);

        foreach ($temp as $index => $entry) {
            $state_group = null;
            if ($state_name == 'ciscoEnvMonTemperatureState' && (empty($entry[$tablevalue['descr']]))) {
                Log::debug('Invalid sensor, skipping..');
            } else {
                //Discover Sensors
                $descr = ucwords($entry[$tablevalue['descr']] ?? 'State');
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
                    $descr = SnmpQuery::get('ENTITY-MIB::entPhysicalName.' . $index)->value();
                } elseif ($state_name == 'c3gModemStatus' || $state_name == 'c3gGsmCurrentBand' || $state_name == 'c3gGsmPacketService' || $state_name == 'c3gGsmCurrentRoamingStatus' || $state_name == 'c3gGsmSimStatus') {
                    $descr = $tablevalue['descr'];
                    $state_group = SnmpQuery::get('ENTITY-MIB::entPhysicalName.' . $index)->value();
                } elseif ($state_name == 'crepSegmentComplete') {
                    $repsegmentnumber++;
                    $descr = $tablevalue['descr'] . $repsegmentnumber;
                }
                discover_sensor(null, 'state', $device, $cur_oid . $index, $index, $state_name, trim($descr), 1, 1, null, null, null, null, $entry[$state_name], 'snmp', $index, null, null, $state_group);
            }
        }
    }
}

unset($role_data, $redundant_data, $temp);
