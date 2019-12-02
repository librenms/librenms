<?php
if ($device['os'] == 'fortimail') {
    echo 'FORTIMAIL-MEMORY-POOL: ';
    // fmlSysMemCapacity: Total physical memory (RAM) installed (KB)
    // Conversion from kilobyte (base 2) to byte (base 10): Precision = 1024
    discover_mempool($valid_mempool, $device, 0, 'fortimail', 'Physical Memory', '1024', null, null);
}
