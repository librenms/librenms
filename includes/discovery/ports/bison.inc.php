<?php

unset($port_stats);

$dpdk_port_stats = SnmpQuery::walk('BISON-ROUTER-MIB::vifTable')->table(1);

$offset = 1000;

foreach ($dpdk_port_stats as $index => $sfpport_stats) {
    $curIfIndex = $offset + $index;
    $port_stats[$curIfIndex]['ifDescr'] = $sfpport_stats['BISON-ROUTER-MIB::vifName'];
    $port_stats[$curIfIndex]['ifName'] = $sfpport_stats['BISON-ROUTER-MIB::vifPort'];
    $port_stats[$curIfIndex]['ifInOctets'] = $sfpport_stats['BISON-ROUTER-MIB::vifRxOctets'];
    $port_stats[$curIfIndex]['ifOutOctets'] = $sfpport_stats['BISON-ROUTER-MIB::vifTxOctets'];
    $port_stats[$curIfIndex]['ifInUcastPkts'] = $sfpport_stats['BISON-ROUTER-MIB::vifRxPkts'];
    $port_stats[$curIfIndex]['ifOutUcastPkts'] = $sfpport_stats['BISON-ROUTER-MIB::vifTxPkts'];
    $port_stats[$curIfIndex]['ifOperStatus'] = 'up';
    $port_stats[$curIfIndex]['ifAdminStatus'] = 'up';
    $port_stats[$curIfIndex]['ifVlan'] = $sfpport_stats['BISON-ROUTER-MIB::vifCvid'];
}

unset($dpdk_port_stats);
