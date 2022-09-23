<?php

$airos_stats = snmpwalk_cache_oid($device, '.1.3.6.1.4.1.41112.1.3.3.1', [], 'UBNT-AirFIBER-MIB');

if (isset($airos_stats[1]['rxOctetsOK'])) {
    foreach ($port_stats as $index => $afport_stats) {
        if ($afport_stats['ifDescr'] == 'eth0') {
            $port_stats[$index]['ifOperStatus'] = 'up'; // if may be marked as down
            $port_stats[$index]['ifInOctets'] = $airos_stats[1]['rxOctetsOK'];
            $port_stats[$index]['ifOutOctets'] = $airos_stats[1]['txOctetsOK'];
            $port_stats[$index]['ifInErrors'] = $airos_stats[1]['rxErroredFrames'];
            $port_stats[$index]['ifOutErrors'] = $airos_stats[1]['txErroredFrames'];
            $port_stats[$index]['ifInBroadcastPkts'] = $airos_stats[1]['rxValidBroadcastFrames'];
            $port_stats[$index]['ifOutBroadcastPkts'] = $airos_stats[1]['txValidBroadcastFrames'];
            $port_stats[$index]['ifInMulticastPkts'] = $airos_stats[1]['rxValidMulticastFrames'];
            $port_stats[$index]['ifOutMulticastPkts'] = $airos_stats[1]['txValidMulticastFrames'];
            $port_stats[$index]['ifInUcastPkts'] = $airos_stats[1]['rxValidUnicastFrames'];
            $port_stats[$index]['ifOutUcastPkts'] = $airos_stats[1]['txValidUnicastFrames'];

            break;
        }
    }
}
