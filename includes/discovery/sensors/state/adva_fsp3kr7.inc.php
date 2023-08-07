<?php
/*
 * Sensor State discovery module for the ADVA FSP3000 R7 Platform
 *
 * © 2023 Khairi Azmi <mkhairi47@hotmail.com>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

echo 'Adva FSP3KR7';

// Module State Sensor
foreach ($pre_cache['adva_fsp3kr7'] as $index => $entry) {
    $module_oid = '.1.3.6.1.4.1.2544.1.11.7.3.3.7.1.62.';
    $module_name = 'moduleOperState';
    $module_states = [
        ['value' => 0, 'generic' => 3, 'graph' => 0, 'descr' => 'UNDEFINED'],
        ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'IN-SERVICE'],
        ['value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'ABNORMAL'],
        ['value' => 3, 'generic' => 1, 'graph' => 0, 'descr' => 'MISMATCH'],
        ['value' => 4, 'generic' => 1, 'graph' => 0, 'descr' => 'UN'],
    ];
    create_state_index($module_name, $module_states);

    $descr = '';

    if ($entry['moduleType'] == 'eqpPsu9hudc') {
        $descr = $entry['entityEqptAidString'] . ' - [PSU 9HU] Status: ';
    } elseif ($entry['moduleType'] == 'eqpEdfaDgcv') {
        $descr = $entry['entityEqptAidString'] . ' - [EDFA] Status: ';
    } elseif ($entry['moduleType'] == 'eqpNcuII') {
        $descr = $entry['entityEqptAidString'] . ' - [NCU II] Status: ';
    } elseif ($entry['moduleType'] == 'eqp2Wcc10g') {
        $descr = $entry['entityEqptAidString'] . ' - [2WCC] Status: ';
    } elseif ($entry['moduleType'] == 'eqpOscmPn') {
        $descr = $entry['entityEqptAidString'] . ' - [OSCM] Status: ';
    } elseif ($entry['moduleType'] == 'eqpOsfm') {
        $descr = $entry['entityEqptAidString'] . ' - [OSFM] Status: ';
    } elseif ($entry['moduleType'] == 'eqp4csmud') {
        $descr = $entry['entityEqptAidString'] . ' - [4CSM] Status: ';
    } elseif ($entry['moduleType'] == 'eqpScuII') {
        $descr = $entry['entityEqptAidString'] . ' - [SCU II] Status: ';
    } elseif ($entry['moduleType'] == 'eqpScuS') {
        $descr = $entry['entityEqptAidString'] . ' - [SCU S] Status: ';
    } elseif ($entry['moduleType'] == 'eqpPsu1hudc') {
        $descr = $entry['entityEqptAidString'] . ' - [PSU 1HU DC] Status: ';
    } elseif ($entry['moduleType'] == 'eqpPsu1huac') {
        $descr = $entry['entityEqptAidString'] . ' - [PSU 1HU AC] Status: ';
    } elseif ($entry['moduleType'] == 'eqpPsu7huac') {
        $descr = $entry['entityEqptAidString'] . ' - [PSU 7HU AC] Status: ';
    }

    if (!empty($descr)) {
        discover_sensor($valid['sensor'], 'state', $device, $module_oid . $index, $index, $module_name, $descr, 1, 1, null, null, null, null, $entry['moduleOperState'], 'snmp', $index, null, null, 'Module State');
        create_sensor_to_state_index($device, $module_name, $index);
    }
}

// Fan State SH9HU/SH1HU
$loopIndex = 1;
$fanOid = '.1.3.6.1.4.1.2544.1.11.7.3.3.3.1.4.';
$fan_name = 'fanAdmin';
$fan_states = [
    ['value' => 1, 'generic' => 3, 'graph' => 0, 'descr' => 'UAS'],
    ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'IN-SERVICE'],
    ['value' => 3, 'generic' => 0, 'graph' => 0, 'descr' => 'AUTO IN-SERVICE'],
    ['value' => 4, 'generic' => 1, 'graph' => 0, 'descr' => 'MANAGEMENT'],
    ['value' => 5, 'generic' => 1, 'graph' => 0, 'descr' => 'MT'],
    ['value' => 6, 'generic' => 2, 'graph' => 0, 'descr' => 'DISABLED'],
    ['value' => 7, 'generic' => 2, 'graph' => 0, 'descr' => 'PPS'],
];

foreach ($pre_cache['adva_fsp3kr7_Fan'] as $index => $entry) {
    if ($entry['fanType'] == 'eqpCem9hu' || $entry['fanType'] == 'eqpFan9hu' || $entry['fanType'] == 'eqpFan1hu') {
        create_state_index($fan_name, $fan_states);

        $descr = ($entry['fanType'] == 'eqpCem9hu')
            ? 'CEM Fan Status'
            : (($entry['fanType'] == 'eqpFan9hu')
                ? '9HU Fan ' . $loopIndex++ . ' Status'
            : (($entry['fanType'] == 'eqpFan1hu')
                ? 'SH1HU Fan Status'
            : 'Default Value')); // Add a default value if none of the conditions match


        discover_sensor($valid['sensor'], 'state', $device, $fanOid . $index, $index, $fan_name, $descr, 1, 1, null, null, null, null, $entry['fanAdmin'], 'snmp', null, null, null, 'Fan State');
        create_sensor_to_state_index($device, $fan_name, $index);
    }
}
