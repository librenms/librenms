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

use App\Models\Sensor;
use App\Models\StateTranslation;
use LibreNMS\Enum\Severity;

$temp = SnmpQuery::get('PowerNet-MIB::upsAdvBatteryReplaceIndicator.0')->value();
$cur_oid = '.1.3.6.1.4.1.318.1.1.1.2.2.4.0';
$index = '0';

if (is_numeric($temp)) {
    app('sensor-discovery')->discover(new Sensor([
        'poller_type' => 'snmp',
        'sensor_class' => 'state',
        'sensor_oid' => $cur_oid,
        'sensor_index' => $index,
        'sensor_type' => 'upsAdvBatteryReplaceIndicator',
        'sensor_descr' => 'UPS Battery Replacement Status',
        'sensor_divisor' => 1,
        'sensor_multiplier' => 1,
        'sensor_current' => $temp,
    ]))->withStateTranslations('upsAdvBatteryReplaceIndicator', [
        StateTranslation::define('noBatteryNeedsReplacing', 1, Severity::Ok),
        StateTranslation::define('batteryNeedsReplacing', 2, Severity::Error),
    ]);
}

$cooling_status = snmpwalk_cache_oid($device, 'coolingUnitStatusDiscreteEntry', [], 'PowerNet-MIB');
foreach ($cooling_status as $index => $data) {
    $cur_oid = '.1.3.6.1.4.1.318.1.1.27.1.4.2.2.1.4.' . $index;
    $state_name = $data['coolingUnitStatusDiscreteDescription'];

    $tmp_states = explode(',', (string) $data['coolingUnitStatusDiscreteIntegerReferenceKey']);
    $translations = [];
    foreach ($tmp_states as $ref) {
        preg_match('/([\w]+) ?\\(([\d]+)\\)/', $ref, $matches);
        $severity = match (get_nagios_state($matches[1])) {
            0 => Severity::Ok,
            1 => Severity::Warning,
            2 => Severity::Error,
            default => Severity::Unknown,
        };
        $translations[] = StateTranslation::define($matches[1], 0, $severity);
    }

    app('sensor-discovery')->discover(new Sensor([
        'poller_type' => 'snmp',
        'sensor_class' => 'state',
        'sensor_oid' => $cur_oid,
        'sensor_index' => $cur_oid,
        'sensor_type' => $state_name,
        'sensor_descr' => $state_name,
        'sensor_divisor' => 1,
        'sensor_multiplier' => 1,
        'sensor_current' => $data['coolingUnitStatusDiscreteValueAsInteger'],
    ]))->withStateTranslations($state_name, $translations);
}

unset($cooling_status);

$cooling_unit = snmpwalk_cache_oid($device, 'coolingUnitExtendedDiscreteEntry', [], 'PowerNet-MIB');
foreach ($cooling_unit as $index => $data) {
    $cur_oid = '.1.3.6.1.4.1.318.1.1.27.1.6.2.2.1.4.' . $index;
    $state_name = $data['coolingUnitExtendedDiscreteDescription'];

    $tmp_states = explode(',', (string) $data['coolingUnitExtendedDiscreteIntegerReferenceKey']);
    $translations = [];
    foreach ($tmp_states as $ref) {
        preg_match('/([\w]+)\\(([\d]+)\\)/', $ref, $matches);
        $severity = match (get_nagios_state($matches[1])) {
            0 => Severity::Ok,
            1 => Severity::Warning,
            2 => Severity::Error,
            default => Severity::Unknown,
        };
        $translations[] = StateTranslation::define($matches[1], 0, $severity);
    }

    app('sensor-discovery')->discover(new Sensor([
        'poller_type' => 'snmp',
        'sensor_class' => 'state',
        'sensor_oid' => $cur_oid,
        'sensor_index' => $cur_oid,
        'sensor_type' => $state_name,
        'sensor_descr' => $state_name,
        'sensor_divisor' => 1,
        'sensor_multiplier' => 1,
        'sensor_current' => $data['coolingUnitExtendedDiscreteValueAsInteger'],
    ]))->withStateTranslations($state_name, $translations);
}

unset($cooling_unit);

$relays = snmpwalk_cache_oid($device, 'emsOutputRelayControlEntry', [], 'PowerNet-MIB');
foreach ($relays as $index => $data) {
    $cur_oid = '.1.3.6.1.4.1.318.1.1.10.3.2.1.1.3.' . $index;
    $state_name = $data['emsOutputRelayControlOutputRelayName'];

    $current = apc_relay_state($data['emsOutputRelayControlOutputRelayCommand']);
    if (is_numeric($current)) {
        app('sensor-discovery')->discover(new Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'state',
            'sensor_oid' => $cur_oid,
            'sensor_index' => $cur_oid,
            'sensor_type' => $state_name,
            'sensor_descr' => $state_name,
            'sensor_divisor' => 1,
            'sensor_multiplier' => 1,
            'sensor_current' => $current,
        ]))->withStateTranslations($state_name, [
            StateTranslation::define('immediateCloseEMS', 1, Severity::Error),
            StateTranslation::define('immediateOpenEMS', 2, Severity::Ok),
        ]);
    }
}
unset(
    $relays,
    $index,
    $data
);

$switched = snmpwalk_cache_oid($device, 'emsOutletControlEntry', [], 'PowerNet-MIB');
foreach ($switched as $index => $data) {
    $cur_oid = '.1.3.6.1.4.1.318.1.1.10.3.3.1.1.3.' . $index;
    $state_name = $data['emsOutletControlOutletName'];

    $current = apc_relay_state($data['emsOutletControlOutletCommand']);
    if (is_numeric($current)) {
        app('sensor-discovery')->discover(new Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'state',
            'sensor_oid' => $cur_oid,
            'sensor_index' => $cur_oid,
            'sensor_type' => $state_name,
            'sensor_descr' => $state_name,
            'sensor_divisor' => 1,
            'sensor_multiplier' => 1,
            'sensor_current' => $current,
        ]))->withStateTranslations($state_name, [
            StateTranslation::define('immediateOnEMS', 1, Severity::Error),
            StateTranslation::define('immediateOffEMS', 2, Severity::Ok),
        ]);
    }
}
unset(
    $switched,
    $index,
    $data
);

foreach ($pre_cache['mem_sensors_status'] as $index => $data) {
    $descr = ! empty($data['memSensorsStatusSensorName'])
        ? $data['memSensorsStatusSensorName'] . ' - ' . ($data['memSensorsStatusSensorLocation'] ?? '')
        : null;

    if ($data['memSensorsCommStatus']) {
        $cur_oid = '.1.3.6.1.4.1.318.1.1.10.4.2.3.1.7.' . $index;
        $current = $data['memSensorsCommStatus'];
        if (is_numeric($current)) {
            app('sensor-discovery')->discover(new Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => 'state',
                'sensor_oid' => $cur_oid,
                'sensor_index' => 'memSensorsCommStatus.' . $index,
                'sensor_type' => 'memSensorsCommStatus',
                'sensor_descr' => $descr ?? 'memSensorsCommStatus',
                'sensor_divisor' => 1,
                'sensor_multiplier' => 1,
                'sensor_current' => $current,
            ]))->withStateTranslations('memSensorsCommStatus', [
                StateTranslation::define('notInstalled', 1, Severity::Warning),
                StateTranslation::define('commsOK', 2, Severity::Ok),
                StateTranslation::define('commsLost', 3, Severity::Error),
            ]);
        }
    }

    if ($data['memSensorsAlarmStatus']) {
        $cur_oid = '.1.3.6.1.4.1.318.1.1.10.4.2.3.1.8.' . $index;
        $current = $data['memSensorsAlarmStatus'];
        if (is_numeric($current)) {
            app('sensor-discovery')->discover(new Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => 'state',
                'sensor_oid' => $cur_oid,
                'sensor_index' => 'memSensorsAlarmStatus.' . $index,
                'sensor_type' => 'memSensorsAlarmStatus',
                'sensor_descr' => $descr ?? 'memSensorsAlarmStatus',
                'sensor_divisor' => 1,
                'sensor_multiplier' => 1,
                'sensor_current' => $current,
            ]))->withStateTranslations('memSensorsAlarmStatus', [
                StateTranslation::define('memNormal', 1, Severity::Ok),
                StateTranslation::define('memWarning', 2, Severity::Warning),
                StateTranslation::define('memCritical', 3, Severity::Error),
            ]);
        }
    }
}

// Monitor contact switches via the UIO ports.
$apcContactData = snmpwalk_cache_oid($device, 'uioInputContact', [], 'PowerNet-MIB', null, '-OQUse');
if (isset($apcContactData['uioInputContactStatusTableSize']) && $apcContactData['uioInputContactStatusTableSize'] > 0) {
    // NMC2/NMC3/etc Universal Input Output
    foreach (array_keys($apcContactData) as $index) {
        // APC disabled (1), enabled (2)
        $current = $apcContactData[$index]['uioInputContactStatusCurrentState'];
        // state 4 is "not applicable"
        if ($current != 4) {
            $cur_oid = '.1.3.6.1.4.1.318.1.1.25.2.2.1.5.' . $index;

            // APC normal (1), warning (2), critical (3), notaplicable (4)
            // LibreNMS warning (1), critical (2)

            $state_name = $apcContactData[$index]['uioInputContactStatusContactName'];

            // universalInputOutput sensor entries all have an sub-index, presumably to allow for multiple sensors in the
            // future. Here we remove the sub-index from the first entry, so 1.1 becomes 1, 2.1 becomes 2, etc. However any
            // future appearing sub-index will remain untouched, so 1.2 will stay 1.2, 2.2 will stay 2.2, etc.
            // The reason that we remove the sub-index from the first entry is to preserve compatibility with sensors
            // created by prior versions using the legacy iemConfig and iemStatus tables.
            $split_index = explode('.', (string) $index);
            if (count($split_index) == 2 && $split_index[1] == 1) {
                $index = $split_index[0];
            }

            app('sensor-discovery')->discover(new Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => 'state',
                'sensor_oid' => $cur_oid,
                'sensor_index' => $state_name . '.' . $index,
                'sensor_type' => $state_name,
                'sensor_descr' => $state_name,
                'sensor_divisor' => 1,
                'sensor_multiplier' => 1,
                'sensor_current' => $current,
            ]))->withStateTranslations($state_name, [
                StateTranslation::define('normal', 1, Severity::Ok),
                StateTranslation::define('warning', 2, Severity::Warning),
                StateTranslation::define('critical', 3, Severity::Error),
            ]);
        }
    }
} else {
    // NMC1 Integrated Environmental Monitor (legacy)
    $apcContactData = snmpwalk_cache_oid($device, 'iemConfigContactsTable', [], 'PowerNet-MIB', null, '-OQUse');
    $apcContactData = snmpwalk_cache_oid($device, 'iemStatusContactsTable', $apcContactData, 'PowerNet-MIB', null, '-OQUse');

    foreach (array_keys($apcContactData) as $index) {
        // APC disabled (1), enabled (2)
        if ($apcContactData[$index]['iemConfigContactEnable'] == 2) {
            $current = $apcContactData[$index]['iemStatusContactStatus'];
            $cur_oid = '.1.3.6.1.4.1.318.1.1.10.2.3.4.1.3.' . $index;
            $severity = $apcContactData[$index]['iemConfigContactSeverity'];

            // APC critical (1), warning (2)
            // LibreNMS warning (1), critical (2)
            $faultSeverity = $severity == 1 ? Severity::Error : Severity::Warning;

            $state_name = $apcContactData[$index]['iemConfigContactName'];

            app('sensor-discovery')->discover(new Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => 'state',
                'sensor_oid' => $cur_oid,
                'sensor_index' => $state_name . '.' . $index,
                'sensor_type' => $state_name,
                'sensor_descr' => $state_name,
                'sensor_divisor' => 1,
                'sensor_multiplier' => 1,
                'sensor_current' => $current,
            ]))->withStateTranslations($state_name, [
                StateTranslation::define('noFault', 1, Severity::Ok),
                StateTranslation::define('fault', 2, $faultSeverity),
                StateTranslation::define('disabled', 3, Severity::Ok),
            ]);
        }
    }
}
