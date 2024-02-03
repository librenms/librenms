<?php
/*
 * LibreNMS - discovery/sensors/current/ericsson-ipos.inc.php
 *
 * Copyright (c) 2024 Rudy Broersma <tozz@kijkt.tv>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$eriRouterOpticalTransceiverParamTable = snmpwalk_group($device, 'eriRouterOpticalTransceiverParamTable', 'ERICSSON-ROUTER-OPTICAL-TRANSCEIVER-MIB', 0);
$eriRouterOpticalTransceiverPortTable =  snmpwalk_group($device, 'eriRouterOpticalTransceiverPortTable', 'ERICSSON-ROUTER-OPTICAL-TRANSCEIVER-MIB', 0);

// Compute list of index that are available in both tables.
$index_list_param = array_keys($eriRouterOpticalTransceiverParamTable['eriRouterOpticalTransceiverCurrentValue']);
$index_list_port = array_keys($eriRouterOpticalTransceiverPortTable['eriRouterOpticalTransceiverCardSlot']);
$index_list = array_intersect($index_list_param, $index_list_port);

foreach($index_list as $index) {
    if (isset($eriRouterOpticalTransceiverParamTable['eriRouterOpticalTransceiverCurrentValue'][$index]['laserBiasCurrent'])) {
        discover_sensor(
            $valid['sensor'],
            'current',
            $device,
            '.1.3.6.1.4.1.193.218.2.50.1.2.1.3.' . $index . '.4',
            'eriRouterOpticalTransceiverCurrentValueBiasCurrent.' . $index,
            'ericsson-ipos',
            'Slot ' . $eriRouterOpticalTransceiverPortTable['eriRouterOpticalTransceiverCardSlot'][$index] . ' / Port ' . $eriRouterOpticalTransceiverPortTable['eriRouterOpticalTransceiverPort'][$index],
            1000000,
            1,
            ($eriRouterOpticalTransceiverParamTable['eriRouterOpticalTransceiverMinAlarmValue'][$index]['laserBiasCurrent'] / 1000),
            ($eriRouterOpticalTransceiverParamTable['eriRouterOpticalTransceiverMinWarningValue'][$index]['laserBiasCurrent'] / 1000),
            ($eriRouterOpticalTransceiverParamTable['eriRouterOpticalTransceiverMaxWarningValue'][$index]['laserBiasCurrent'] / 1000),
            ($eriRouterOpticalTransceiverParamTable['eriRouterOpticalTransceiverMaxAlarmValue'][$index]['laserBiasCurrent'] / 1000),
            ($eriRouterOpticalTransceiverParamTable['eriRouterOpticalTransceiverCurrentValue'][$index]['laserBiasCurrent'] / 1000),
            'snmp',
            null,
            null,
            null,
            'Optic Bias Current'
        );
    }
}

