<?php

$tcstStat_port_stats = SnmpQuery::walk('MOXA-TCST-MIB::tcstStatGroupTable')->table(1);

foreach ($tcstStat_port_stats as $index => $moxaport_stats) {
    //print_r($moxaport_stats);
    $port_stats[$index]['ifInOctets'] = $moxaport_stats['MOXA-TCST-MIB::tcstStatRxTotalPackets'];
    $port_stats[$index]['ifInUcastPkts'] = $moxaport_stats['MOXA-TCST-MIB::tcstStatRxUnicastPackets'];
    $port_stats[$index]['ifInErrors'] = $moxaport_stats['MOXA-TCST-MIB::tcstStatRxErrorsPackets'];
    $port_stats[$index]['ifOutOctets'] = $moxaport_stats['MOXA-TCST-MIB::tcstStatTxTotalOctets'];
    $port_stats[$index]['ifOutUcastPkts'] = $moxaport_stats['MOXA-TCST-MIB::tcstStatTxUnicastPackets'];
    $port_stats[$index]['ifOutErrors'] = $moxaport_stats['MOXA-TCST-MIB::tcstStatTxErrorsPackets'];
}

unset($tcstStat_port_stats);
