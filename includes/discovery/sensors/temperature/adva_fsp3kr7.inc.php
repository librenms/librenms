<?php
/**
 * LibreNMS - ADVA device support - Temperature Sensors
 *
 * @category   Network_Monitoring
 *
 * @author     Christoph Zilian <czilian@hotmail.com> && Khairi Azmi <mkhairi47@hotmail.com>
 * @license    https://gnu.org/copyleft/gpl.html GNU GPL
 *
 * @link       https://github.com/librenms/librenms/
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 **/

// *************************************************************
// ***** Temperature Sensors for ADVA FSP3000 R7
// *************************************************************

$multiplier = 1;
$divisor = 10;

// Module temperature
foreach ($pre_cache['adva_fsp3kr7_Card'] as $index => $entry) {
    $oid = '.1.3.6.1.4.1.2544.1.11.11.1.2.1.1.1.5.' . $index;
    $entityType = $entry['entityEqptType'];
    $descrPrefix = $entry['entityEqptAidString'];

    if ($entityType == 'eqpCem9hu') {
        $entityDescr = 'CEM 9HU';
    } elseif ($entityType == 'eqpPsu9hudc') {
        $entityDescr = 'PSU 9HU';
    } elseif ($entityType == 'eqpOscmPn') {
        $entityDescr = 'OSCM';
    } elseif ($entityType == 'eqpOsfm') {
        $entityDescr = 'OSFM';
    } elseif ($entityType == 'eqpEdfaDgcv') {
        $entityDescr = 'EDFA';
    } elseif ($entityType == 'eqp2Wcc10g') {
        $entityDescr = '2WCC';
    } elseif ($entityType == 'eqp4csmud') {
        $entityDescr = '4CSM';
    } elseif ($entityType == 'eqpNcuII') {
        $entityDescr = 'NCU II';
    } elseif ($entityType == 'eqpScuII') {
        $entityDescr = 'SCU II';
    } elseif ($entityType == 'eqpSh1upf') {
        $entityDescr = 'SH1HU';
    } elseif ($entityType == 'eqpScuS') {
        $entityDescr = 'SCU S';
    } elseif ($entityType == 'eqpPsu1hudc') {
        $entityDescr = 'PSU 1HU DC';
    } elseif ($entityType == 'eqpPsu1huac') {
        $entityDescr = 'PSU 1HU AC';
    } elseif ($entityType == 'eqpPsu7huac') {
        $entityDescr = 'PSU 7HU AC';
    } else {
        continue; // Skip unknown types
    }

    $descr = $descrPrefix . ' - [' . $entityDescr . '] Temp: ';
    $value = $entry['eqptPhysInstValueTemp'] / $divisor;
    $high_limit = $entry['eqptPhysThresholdTempHigh'] / $divisor;

    discover_sensor(
        $valid['sensor'],
        'temperature',
        $device,
        $oid,
        'eqptPhysInstValueTemp' . $index,
        'adva_fsp3kr7',
        $descr,
        $divisor,
        $multiplier,
        null,
        null,
        null,
        $high_limit,
        $value
    );
}
