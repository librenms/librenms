<?php

unset($port_stats);

$sfp_stats = snmpwalk_group($device, 'ubntSfpsTable', 'UBNT-UFIBER-MIB');

$offset = '1000';

foreach ($sfp_stats as $index => $sfpport_stats) {
    $curIfIndex = $offset + $index;
    $port_stats[$curIfIndex]['ifDescr'] = $sfpport_stats['ubntSfpName'];
    $port_stats[$curIfIndex]['ifName'] = $sfpport_stats['ubntSfpName'];
    $port_stats[$curIfIndex]['ifInOctets'] = $sfpport_stats['ubntSfpRxBytes'];
    $port_stats[$curIfIndex]['ifOutOctets'] = $sfpport_stats['ubntSfpTxBytes'];
    $port_stats[$curIfIndex]['ifOperStatus'] = ($sfpport_stats['ubntSfpUp'] == 1 ? 'up' : 'down');
}

unset($sfp_stats);
