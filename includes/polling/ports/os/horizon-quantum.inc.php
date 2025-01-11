<?php

$hzqtm_stats = snmpwalk_group($device, 'hzQtmEnetPortConfigTable', 'DRAGONWAVE-HORIZON-QUANTUM-MIB', 2);
$hzqtm_stats = snmpwalk_group($device, 'hzQtmEnetPortStatsTable', 'DRAGONWAVE-HORIZON-QUANTUM-MIB', 2, $hzqtm_stats);

d_echo($hzqtm_stats);

$required = [
    'ifName' => 'hzQtmEnetPortName',
    'ifMtu' => 'hzQtmEnetPortMaxFrameSize',
    'ifInOctets' => 'hzQtmEnetPortRxBytes',
    'ifOutOctets' => 'hzQtmEnetPortTxBytes',
    'ifInUcastPkts' => 'hzQtmEnetPortRxUcastPkts',
    'ifOutUcastPkts' => 'hzQtmEnetPortTxUcastPkts',
    'ifInErrors' => 'hzQtmEnetPortRxErrors',
    'ifOutErrors' => 'hzQtmEnetPortTxErrors',
];

$hzqtmPortSpeed = [
    1 => '10000000',
    2 => '100000000',
    3 => '1000000000',
    4 => '1000000000',
];

$hzqtm_ports = [];
foreach ($hzqtm_stats as $index => $port) {
    foreach ($required as $key => $val) {
        $hzqtm_ports[$index][$key] = $hzqtm_stats[$index][$val];
    }
    $hzqtm_ports[$index]['ifDescr'] = $port['ifName'];
    $hzqtm_ports[$index]['ifType'] = 'ethernetCsmacd';
    $hzqtm_ports[$index]['ifOperStatus'] = $port['hzQtmEnetPortLinkStatus'] == 1 ? 'up' : 'down';
    $hzqtm_ports[$index]['ifAdminStatus'] = $port['hzQtmEnetPortAdminState'] == 1 ? 'up' : 'down';
    $hzqtm_ports[$index]['ifSpeed'] = $hzqtmPortSpeed[$port['hzQtmEnetPortSpeed']];
}
$port_stats = array_replace_recursive($hzqtm_ports, $port_stats);
unset($hzqtm_ports);
