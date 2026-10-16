<?php

$routeros_port_stats = SnmpQuery::walk('MIKROTIK-MIB::mtxrInterfaceStatsTable')->table(1);

foreach ($routeros_port_stats as $index => $routeros_stats) {
    //in
    if (isset($routeros_stats['MIKROTIK-MIB::mtxrInterfaceStatsRxMulticast'])) {
        $port_stats[$index]['ifHCInMulticastPkts'] = $routeros_stats['MIKROTIK-MIB::mtxrInterfaceStatsRxMulticast'];
    }
    if (isset($routeros_stats['MIKROTIK-MIB::mtxrInterfaceStatsRxBroadcast'])) {
        $port_stats[$index]['ifHCInBroadcastPkts'] = $routeros_stats['MIKROTIK-MIB::mtxrInterfaceStatsRxBroadcast'];
    }
    if (isset($routeros_stats['MIKROTIK-MIB::mtxrInterfaceStatsRxFCSError'])) {
        $port_stats[$index]['ifInErrors'] = $routeros_stats['MIKROTIK-MIB::mtxrInterfaceStatsRxFCSError'];
    }
    if (isset($routeros_stats['MIKROTIK-MIB::mtxrInterfaceStatsRxDrop'])) {
        $port_stats[$index]['ifInDiscards'] = $routeros_stats['MIKROTIK-MIB::mtxrInterfaceStatsRxDrop'];
    }
    //out
    if (isset($routeros_stats['MIKROTIK-MIB::mtxrInterfaceStatsTxMulticast'])) {
        $port_stats[$index]['ifHCOutMulticastPkts'] = $routeros_stats['MIKROTIK-MIB::mtxrInterfaceStatsTxMulticast'];
    }
    if (isset($routeros_stats['MIKROTIK-MIB::mtxrInterfaceStatsTxBroadcast'])) {
        $port_stats[$index]['ifHCOutBroadcastPkts'] = $routeros_stats['MIKROTIK-MIB::mtxrInterfaceStatsTxBroadcast'];
    }
    if (isset($routeros_stats['MIKROTIK-MIB::mtxrInterfaceStatsTxFCSError'])) {
        $port_stats[$index]['ifOutErrors'] = $routeros_stats['MIKROTIK-MIB::mtxrInterfaceStatsTxFCSError'];
    }
    if (isset($routeros_stats['MIKROTIK-MIB::mtxrInterfaceStatsTxDrop'])) {
        $port_stats[$index]['ifOutDiscards'] = $routeros_stats['MIKROTIK-MIB::mtxrInterfaceStatsTxDrop'];
    }
}

unset($routeros_port_stats);
