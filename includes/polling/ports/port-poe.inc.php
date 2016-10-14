<?php

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
    $rrd_def = array(
        'DS:PortPwrAllocated:GAUGE:600:0:U',
        'DS:PortPwrAvailable:GAUGE:600:0:U',
        'DS:PortConsumption:DERIVE:600:0:U',
        'DS:PortMaxPwrDrawn:GAUGE:600:0:U'
    );

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
