<?php

if (($device['os_group'] == 'fortimail') || ($device['os'] == 'fortimail')) {
    echo 'FORTIMAIL-MEMORY-POOL: ';
    discover_mempool($valid_mempool, $device, 0, 'fortimail', 'Main Memory', '1', null, null);
}
