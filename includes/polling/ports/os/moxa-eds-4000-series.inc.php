<?php

$tcstStat_port_stats = SnmpQuery::walk('MOXA-TCST-MIB::tcstStatGroupTable')->table(1);

foreach ($tcstStat_port_stats as $index => $moxaport_stats) {
    #print_r($moxaport_stats);
    $port_stats[$index]['ifInOctets'] = $moxaport_stats['MOXA-TCST-MIB::tcstStatRxTotalPackets'];
    $port_stats[$index]['ifInUcastPkts'] = $moxaport_stats['MOXA-TCST-MIB::tcstStatRxUnicastPackets'];
    $port_stats[$index]['ifInNUcastPkts'] = $moxaport_stats['MOXA-TCST-MIB::tcstStatRxMulticastPackets'];
    $port_stats[$index]['ifHCInBroadcastPkts'] = $moxaport_stats['MOXA-TCST-MIB::tcstStatRxBroadcastPackets'];
    $port_stats[$index]['ifInErrors'] = $moxaport_stats['MOXA-TCST-MIB::tcstStatRxErrorsPackets'];
    $port_stats[$index]['ifInDiscards'] = $moxaport_stats['MOXA-TCST-MIB::tcstStatRxUnknownProtosPackets'];
    $port_stats[$index]['ifOutOctets'] = $moxaport_stats['MOXA-TCST-MIB::tcstStatTxTotalOctets'];
    $port_stats[$index]['ifOutUcastPkts'] = $moxaport_stats['MOXA-TCST-MIB::tcstStatTxUnicastPackets'];
    $port_stats[$index]['ifOutNUcastPkts'] = $moxaport_stats['MOXA-TCST-MIB::tcstStatTxMulticastPackets'];
    $port_stats[$index]['ifHCOutBroadcastPkts'] = $moxaport_stats['MOXA-TCST-MIB::tcstStatTxBroadcastPackets'];
    $port_stats[$index]['ifOutErrors'] = $moxaport_stats['MOXA-TCST-MIB::tcstStatTxErrorsPackets'];
    $port_stats[$index]['ifOutDiscards'] = $moxaport_stats['MOXA-TCST-MIB::tcstStatTxDiscardsPackets'];
}

unset($tcstStat_port_stats);
