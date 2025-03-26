<?php

unset($port_stats);

$dpdk_port_stats = snmpwalk_group($device, 'vifTable', 'BISON-ROUTER-MIB');

$offset = '1000';

foreach ($dpdk_port_stats as $index => $sfpport_stats) {
    $curIfIndex = $offset + $index;
    $port_stats[$curIfIndex]['ifDescr'] = $sfpport_stats['vifName'];
    $port_stats[$curIfIndex]['ifName'] = $sfpport_stats['vifPort'];
    $port_stats[$curIfIndex]['ifInOctets'] = $sfpport_stats['vifRxOctets'];
    $port_stats[$curIfIndex]['ifOutOctets'] = $sfpport_stats['vifTxOctets'];
    $port_stats[$curIfIndex]['ifInUcastPkts'] = $sfpport_stats['vifRxPkts'];
    $port_stats[$curIfIndex]['ifOutUcastPkts'] = $sfpport_stats['vifTxPkts'];
    $port_stats[$curIfIndex]['ifOperStatus'] = 'up';
    $port_stats[$curIfIndex]['ifAdminStatus'] = 'up';
    $port_stats[$curIfIndex]['ifVlan'] = $sfpport_stats['vifCvid'];
}

unset($dpdk_port_stats);
