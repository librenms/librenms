<?php

if (($device['os_group'] == 'fortigate') || ($device['os'] == 'fortigate')) {
    echo 'FORTIGATE-MEMORY-POOL: ';
    discover_mempool($valid_mempool, $device, 0, 'fortigate', 'Main Memory', '1', null, null);
}
