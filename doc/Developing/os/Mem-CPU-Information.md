source: Developing/os/Mem-CPU-Information.md

This document will guide you through adding detection for Memory / Processor for your new device.

#### Memory

Detection for memory is done via two php scripts, one for discovery and the other for polling:

`includes/discovery/mempools/pulse.inc.php`

```php
<?php

if ($device['os'] === 'pulse') {
    echo 'PULSE-MEMORY-POOL: ';

    $usage = str_replace('"', "", snmp_get($device, 'iveMemoryUtil.0', '-OvQ', 'PULSESECURE-PSG-MIB'));

    if (is_numeric($usage)) {
        discover_mempool($valid_mempool, $device, 0, 'pulse-mem', 'Main Memory', '100', null, null);
    }
}
```

`includes/polling/mempools/pulse.inc.php`

```php
<?php

echo 'Pulse Secure MemPool\n';

$perc = str_replace('"', "", snmp_get($device, "iveMemoryUtil.0", '-OvQ', 'PULSESECURE-PSG-MIB'));

if (is_numeric($perc)) {
    $memory_available = str_replace('"', "", snmp_get($device, "memTotalReal.0", '-OvQ', 'UCD-SNMP-MIB'));
    $mempool['total'] = $memory_available;
    $mempool['used'] = ($memory_available / 100 * $perc);
    $mempool['free'] = ($memory_available - $mempool['used']);
}
```

#### Processor

Detection for processors is done via a single script unless custom processing of data is required (as in this example).

`includes/discovery/processors/pulse.inc.php`

```php
<?php

if ($device['os'] === 'pulse') {
    echo 'Pulse Secure : ';

    $descr = 'Processor';
    $usage = str_replace('"', "", snmp_get($device, 'iveCpuUtil.0', '-OvQ', 'PULSESECURE-PSG-MIB'));

    if (is_numeric($usage)) {
        discover_processor($valid['processor'], $device, 'iveCpuUtil.0', '0', 'pulse-cpu', $descr, '100', $usage, null, null);
    }
}
```

`includes/polling/processors/pulse.inc.php`

```php
<?php

echo 'Pulse Secure CPU Usage';

$usage = str_replace('"', "", snmp_get($device, 'iveCpuUtil.0', '-OvQ', 'PULSESECURE-PSG-MIB'));

if (is_numeric($usage)) {
    $proc = ($usage * 100);
}
```
