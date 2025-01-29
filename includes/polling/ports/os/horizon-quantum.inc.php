<?php

$hzqtm_stats = snmpwalk_group($device, 'hzQtmEnetPortConfigTable', 'DRAGONWAVE-HORIZON-QUANTUM-MIB');
$hzqtm_stats = snmpwalk_group($device, 'hzQtmEnetPortStatsTable', 'DRAGONWAVE-HORIZON-QUANTUM-MIB', 1, $hzqtm_stats);
$hzqtm_stats = snmpwalk_group($device, 'hzQtmEnetPortStatusTable', 'DRAGONWAVE-HORIZON-QUANTUM-MIB', 1, $hzqtm_stats);

d_echo($hzqtm_stats);

$required = [
    'ifName' => 'hzQtmEnetPortName',
    'ifDescr' => 'hzQtmEnetPortName',
    'ifMtu' => 'hzQtmEnetPortMaxFrameSize',
    'ifInOctets' => 'hzQtmEnetPortRxBytes',
    'ifOutOctets' => 'hzQtmEnetPortTxBytes',
    'ifInUcastPkts' => 'hzQtmEnetPortRxUcastPkts',
    'ifOutUcastPkts' => 'hzQtmEnetPortTxUcastPkts',
    'ifInNUcastPkts' => 'hzQtmEnetPortRxNUcastPkts',
    'ifOutNUcastPkts' => 'hzQtmEnetPortTxNUcastPkts',
    'ifInErrors' => 'hzQtmEnetPortRxErrors',
    'ifOutErrors' => 'hzQtmEnetPortTxErrors',
    'ifInDiscards' => 'hzQtmEnetPortRxDiscards',
    'ifOutDiscards' => 'hzQtmEnetPortTxDiscards',
    'ifInUnknownProtos' => 'hzQtmEnetPortRxUnknownProtos',
];

$hzqtmPortSpeed = [
    1 => ['10000000', '10'],
    2 => ['100000000', '100'],
    3 => ['1000000000', '1000'],
    4 => ['1000000000', '1000'],
];

foreach ($hzqtm_stats as $index => $port) {
    foreach ($required as $key => $val) {
        $port_stats[$port['hzQtmEnetPortIndex']][$key] = $hzqtm_stats[$index][$val];
    }
    $port_stats[$port['hzQtmEnetPortIndex']]['ifOperStatus'] = $port['hzQtmEnetPortLinkStatus'] == 2 ? 'up' : 'down';
    $port_stats[$port['hzQtmEnetPortIndex']]['ifAdminStatus'] = $port['hzQtmEnetPortAdminState'] == 1 ? 'up' : 'down';
    $port_stats[$port['hzQtmEnetPortIndex']]['ifSpeed'] = $hzqtmPortSpeed[$port['hzQtmEnetPortSpeed']][0];
    $port_stats[$port['hzQtmEnetPortIndex']]['ifHighSpeed'] = $hzqtmPortSpeed[$port['hzQtmEnetPortSpeed']][1];
}

unset($hzqtm_stats);
