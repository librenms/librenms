<?php
$airos_stats = snmpwalk_cache_oid($device, '.1.3.6.1.4.1.41112.1.10.1.6', array(), 'UBNT-AFLTU-MIB');

if (isset($airos_stats[0]['afLTUethConnected'])) {
    foreach ($port_stats as $index => $afport_stats) {
        if ($afport_stats['ifDescr'] == 'eth0') {
            $port_stats[$index]['ifOperStatus'] = ($airos_stats[0]['afLTUethConnected'] == "connected" ? "up" : "down");
            $port_stats[$index]['ifHCInOctets'] = $airos_stats[0]['afLTUethRxBytes'];
            $port_stats[$index]['ifHCOutOctets'] = $airos_stats[0]['afLTUethTxBytes'];
            $port_stats[$index]['ifHCInUcastPkts'] = $airos_stats[0]['afLTUethRxPps'];
            $port_stats[$index]['ifHCOutUcastPkts'] = $airos_stats[0]['afLTUethTxPps'];
            $port_stats[$index]['ifHighSpeed'] = '1000';

            break;
        }
    }
}
