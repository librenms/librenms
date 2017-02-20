<?php

use LibreNMS\RRD\RrdDefinition;

$cpe_oids = array(
    'cpeExtPsePortEnable',
    'cpeExtPsePortDiscoverMode',
    'cpeExtPsePortDeviceDetected',
    'cpeExtPsePortIeeePd',
    'cpeExtPsePortAdditionalStatus',
    'cpeExtPsePortPwrMax',
    'cpeExtPsePortPwrAllocated',
    'cpeExtPsePortPwrAvailable',
    'cpeExtPsePortPwrConsumption',
    'cpeExtPsePortMaxPwrDrawn',
    'cpeExtPsePortEntPhyIndex',
    'cpeExtPsePortEntPhyIndex',
    'cpeExtPsePortPolicingCapable',
    'cpeExtPsePortPolicingEnable',
    'cpeExtPsePortPolicingAction',
    'cpeExtPsePortPwrManAlloc',
);

$peth_oids = array(
    'pethPsePortAdminEnable',
    'pethPsePortPowerPairsControlAbility',
    'pethPsePortPowerPairs',
    'pethPsePortDetectionStatus',
    'pethPsePortPowerPriority',
    'pethPsePortMPSAbsentCounter',
    'pethPsePortType',
    'pethPsePortPowerClassifications',
    'pethPsePortInvalidSignatureCounter',
    'pethPsePortPowerDeniedCounter',
    'pethPsePortOverLoadCounter',
    'pethPsePortShortCounter',
    'pethMainPseConsumptionPower',
);

if ($this_port['dot3StatsIndex'] && $port['ifType'] == 'ethernetCsmacd') {
    $rrd_name = getPortRrdName($port_id, 'poe');
    $rrd_def = RrdDefinition::make()
        ->addDataset('PortPwrAllocated', 'GAUGE', 0)
        ->addDataset('PortPwrAvailable', 'GAUGE', 0)
        ->addDataset('PortConsumption', 'DERIVE', 0)
        ->addDataset('PortMaxPwrDrawn', 'GAUGE', 0);

    $upd = "$polled:".$port['cpeExtPsePortPwrAllocated'].':'.$port['cpeExtPsePortPwrAvailable'].':'.$port['cpeExtPsePortPwrConsumption'].':'.$port['cpeExtPsePortMaxPwrDrawn'];

    $fields = array(
        'PortPwrAllocated'   => $port['cpeExtPsePortPwrAllocated'],
        'PortPwrAvailable'   => $port['cpeExtPsePortPwrAvailable'],
        'PortConsumption'    => $port['cpeExtPsePortPwrConsumption'],
        'PortMaxPwrDrawn'    => $port['cpeExtPsePortMaxPwrDrawn'],
    );

    $tags = compact('ifName', 'rrd_name', 'rrd_def');
    data_update($device, 'poe', $tags, $fields);

    echo 'PoE ';
}//end if
