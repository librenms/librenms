<?php

// The Trellix NSP does not use the IF-MIB
// port information (speed, duplex, operational status, etc...) is kept in TRELLIX-SENSOR-CONF-MIB::intfPortTable
// port statistics (throughput, number of packets, etc...) is kept in TRELLIX-SENSOR-PERF-MIB::intfPortIfTable

// intfPortTable and intfPortIfTable are indexed on slot and port
$mlos_stats = snmpwalk_group($device, 'intfPortTable', 'TRELLIX-SENSOR-CONF-MIB', 2);
$mlos_stats = snmpwalk_group($device, 'intfPortIf64Table', 'TRELLIX-SENSOR-PERF-MIB', 2, $mlos_stats);

$required = [
    'ifInOctets' => 'intfPortTotalBytesRecv64',
    'ifOutOctets' => 'intfPortTotalBytesSent64',
    'ifInUcastPkts' => 'intfPortTotalUnicastPktsRecv64',
    'ifOutUcastPkts' => 'intfPortTotalUnicastPktsSent64',
    'ifInErrors' => 'intfPortTotalCRCErrorsRecv64',
    'ifOutErrors' => 'intfPortTotalCRCErrorsSent64',
];

// port speed, as described in intfPortSpeed
$trellixPortSpeeds = [1 => '10000000',
    2 => '100000000',
    3 => '1000000000',
    4 => '10000000000',
    5 => '40000000000'];

$mlos_ports = [];

// ports are indexed by slot then port, loop over the snmpwalk results and gather the information we want for each port
foreach ($mlos_stats as $slotIndex => $slot) {
    foreach ($slot as $portIndex => $port) {
        $mlos_port = [];
        foreach ($required as $ifEntry => $IfxStat) {
            $mlos_port[$ifEntry] = $mlos_stats[$slotIndex][$portIndex][$IfxStat];
        }
        $mlos_port['ifName'] = $port['intfPortIfDescr'];
        $mlos_port['ifDescr'] = $port['intfPortIfDescr'];
        $mlos_port['ifDuplex'] = ($mlos_stats[$slotIndex][$portIndex]['intfPortEnableFullDuplex'] == 1 ? 'fullDuplex' : 'halfDuplex');
        if (array_key_exists($mlos_stats[$slotIndex][$portIndex]['intfPortSpeed'], $trellixPortSpeeds)) {
            $mlos_port['ifSpeed'] = $trellixPortSpeeds[$mlos_stats[$slotIndex][$portIndex]['intfPortSpeed']];
        }
        $mlos_port['ifOperStatus'] = ($mlos_stats[$slotIndex][$portIndex]['intfPortIfOperStatus'] == 1 ? 'up' : 'down');
        $mlos_port['ifType'] = 'ethernetCsmacd';
        $mlos_port['ifAdminStatus'] = ($mlos_stats[$slotIndex][$portIndex]['intfPortIfAdminStatus'] == 1 ? 'up' : 'down');
        array_push($mlos_ports, $mlos_port);
    }
}

$port_stats = array_replace_recursive($mlos_ports, $port_stats);
unset($mlos_ports);
