<?php

unset($port_stats);

$sfp_stats = snmpwalk_group($device, 'ubntSfps', 'UBNT-UFIBER-MIB', 1, $sfp_stats);

$index = 0;
for ($index = 0; $index < count($sfp_stats); $index++) {
    $port_stats[$index + 1]['ifDescr'] = $sfp_stats[$index]['ubntSfpName'];
    $port_stats[$index + 1]['ifName'] = $sfp_stats[$index]['ubntSfpName'];
    $port_stats[$index + 1]['ifInOctets'] = $sfp_stats[$index]['ubntSfpRxBytes'];
    $port_stats[$index + 1]['ifOutOctets'] = $sfp_stats[$index]['ubntSfpTxBytes'];
    $port_stats[$index + 1]['ifOperStatus'] = ($sfp_stats[$index]['ubntSfpUp'] == 1 ? 'up' : 'down');
}

unset($sfp_stats);
