<?php

$offset = 1000;

$wireless_stats = SnmpQuery::cache()
    ->hideMib()
    ->walk('TACHYON-MIB::wirelessPeersTable')
    ->table(1);

foreach ($wireless_stats as $index => $wireless_entry) {
    $cleanIndex = (int) filter_var($index, FILTER_SANITIZE_NUMBER_INT);
    $curIfIndex = $offset + $cleanIndex;

    $port_stats[$curIfIndex]['ifPhysAddress'] = strtolower(str_replace(':', '', $wireless_entry['wirelessPeerMac'] ?? null));
    $port_stats[$curIfIndex]['ifDescr'] = $wireless_entry['wirelessPeerName'] ?? null;
    $port_stats[$curIfIndex]['ifName'] = $wireless_entry['wirelessPeerName'] ?? null;
    $port_stats[$curIfIndex]['ifSpeed'] = ($wireless_entry['wirelessPeerTxRate'] ?? null) * 1000000;
    $port_stats[$curIfIndex]['ifType'] = 'ieee80211';
    $port_stats[$curIfIndex]['ifInOctets'] = $wireless_entry['wirelessPeerRxBytes'] ?? null;
    $port_stats[$curIfIndex]['ifOutOctets'] = $wireless_entry['wirelessPeerTxBytes'] ?? null;
    $port_stats[$curIfIndex]['ifInUcastPkts'] = $wireless_entry['wirelessPeerRxPackets'] ?? null;
    $port_stats[$curIfIndex]['ifOutUcastPkts'] = $wireless_entry['wirelessPeerTxPackets'] ?? null;
    $port_stats[$curIfIndex]['ifOperStatus'] = ($wireless_entry['wirelessPeerLinkUptime'] ?? 0) > 0 ? 'up' : 'down';
    $port_stats[$curIfIndex]['ifAdminStatus'] = ($wireless_entry['wirelessPeerLinkUptime'] ?? 0) > 0 ? 'up' : 'down';
}

unset($wireless_stats);
