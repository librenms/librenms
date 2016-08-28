<?php

if ($device['os'] == 'ironware' || $device['os_type'] == 'ironware') {
    $is_netiron = snmp_get($device, 'sysObjectID.0', '-OvQ', 'FOUNDRY-SN-AGENT-MIB');

    if (strpos($is_netiron, 'NI') === false && strpos($is_netiron, 'MLX') === false && strpos($is_netiron, 'Cer') === false) {
        echo 'Ironware Dynamic: ';

        $percent = snmp_get($device, 'snAgGblDynMemUtil.0', '-OvQ', 'FOUNDRY-SN-AGENT-MIB');

        if (is_numeric($percent)) {
            discover_mempool($valid_mempool, $device, 0, 'ironware-dyn', 'Dynamic Memory', '1', null, null);
        } //end_if
    } //end_if
    else {
        echo 'NetIron: ';

        d_echo('caching');
        $ni_mempools_array = snmpwalk_cache_multi_oid($device, 'snAgentBrdMainBrdDescription', $ni_mempools_array, 'FOUNDRY-SN-AGENT-MIB', $config['install_dir'].'/mibs');
        $ni_mempools_array = snmpwalk_cache_multi_oid($device, 'snAgentBrdMemoryUtil100thPercent', $ni_mempools_array, 'FOUNDRY-SN-AGENT-MIB', $config['install_dir'].'/mibs');
        $ni_mempools_array = snmpwalk_cache_multi_oid($device, 'snAgentBrdMemoryAvailable', $ni_mempools_array, 'FOUNDRY-SN-AGENT-MIB', $config['install_dir'].'/mibs');
        $ni_mempools_array = snmpwalk_cache_multi_oid($device, 'snAgentBrdMemoryTotal', $ni_mempools_array, 'FOUNDRY-SN-AGENT-MIB', $config['install_dir'].'/mibs');
        d_echo($ni_mempool_array);

        if (is_array($ni_mempools_array)) {
            foreach ($ni_mempools_array as $index => $entry) {
                d_echo($index.' '.$entry['snAgentBrdMainBrdDescription'].' -> '.$entry['snAgentBrdMemoryUtil100thPercent']."\n");

                $usage_oid = '.1.3.6.1.4.1.1991.1.1.2.2.1.1.28.'.$index;
                $descr     = $entry['snAgentBrdMainBrdDescription'];
                $usage     = ($entry['snAgentBrdMemoryUtil100thPercent'] / 100);
                if (!strstr($descr, 'No') && !strstr($usage, 'No') && $descr != '') {
                    discover_mempool($valid_mempool, $device, $index, 'ironware-dyn', $descr, '1', null, null);
                } //end_if
            } //end_foreach
        } //end_if
    } //end_else
} //end_if
