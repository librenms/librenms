<?php

$oid = $mempool['mempool_index'];

d_echo('Ironware Mempool'."\n");

if (str_contains($device['sysDescr'], array('NetIron', 'MLX', 'CER')) === false) {
    echo 'Ironware Dynamic: ';
    $mempool['total'] = snmp_get($device, 'snAgGblDynMemTotal.0', '-OvQ', 'FOUNDRY-SN-AGENT-MIB');
    if ($mempool['total'] < 0) {
        $mempool['total'] =+ 4294967296 ; // signed vs unsigned snmp output
    }
    $mempool['free']  = snmp_get($device, 'snAgGblDynMemFree.0', '-OvQ', 'FOUNDRY-SN-AGENT-MIB');
    if ($mempool['free'] < 0) {
        $mempool['free'] =+ 4294967296 ; // signed vs unsigned snmp output
    }
    $mempool['used']  = ($mempool['total'] - $mempool['free']);
    d_echo($mempool);
} //end_if

else {
    echo 'NetIron: ';
    d_echo('caching');
    $mempool_cache['ironware-dyn'] = array();
    $mempool_cache['ironware-dyn'] = snmpwalk_cache_multi_oid($device, 'snAgentBrdMemoryUtil100thPercent', $mempool_cache['ironware-dyn'], 'FOUNDRY-SN-AGENT-MIB');
    $mempool_cache['ironware-dyn'] = snmpwalk_cache_multi_oid($device, 'snAgentBrdMemoryAvailable', $mempool_cache['ironware-dyn'], 'FOUNDRY-SN-AGENT-MIB');
    $mempool_cache['ironware-dyn'] = snmpwalk_cache_multi_oid($device, 'snAgentBrdMemoryTotal', $mempool_cache['ironware-dyn'], 'FOUNDRY-SN-AGENT-MIB');
    d_echo($mempool_cache);

    $entry = $mempool_cache['ironware-dyn'][$mempool['mempool_index']];

    $perc = $entry['snAgentBrdMemoryUtil100thPercent'];

    $memory_available = $entry['snAgentBrdMemoryTotal'];

    $mempool['total'] = $memory_available;
    $mempool['used']  = $memory_available / 10000 * $perc;
    $mempool['free']  = $memory_available - $mempool['used'];
} //end_else
