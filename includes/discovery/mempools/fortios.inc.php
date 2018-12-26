<?php

if ($device['os'] == 'fortios') {
    d_echo('FortiOS Memory:');
    $temp_data = snmp_get_multi_oid($device, ['fmSysMemCapacity.0', 'fmSysMemUsed.0'], '-OUQs', 'FORTINET-FORTIMANAGER-FORTIANALYZER-MIB');
    if ((is_numeric($temp_data['fmSysMemCapacity.0'])) && (is_numeric($temp_data['fmSysMemUsed.0']))) {
        discover_mempool($valid_mempool, $device, 0, 'fortios', 'Main Memory', '1', null, null);
    }
    unset($temp_data);
}
