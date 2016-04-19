<?php

if ($device['os'] == 'serveriron') {
    echo 'ServerIron Dynamic: ';

    $percent = snmp_get($device, 'snAgGblDynMemUtil.0', '-OvQ', 'FOUNDRY-SN-AGENT-MIB');

    if (is_numeric($percent)) {
        discover_mempool($valid_mempool, $device, 0, 'serveriron', 'Dynamic Memory', '1', null, null);
    }
}
