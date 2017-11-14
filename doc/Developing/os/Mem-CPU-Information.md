source: Developing/os/Mem-CPU-Information.md

This document will guide you through adding detection for Memory / Processor for your new device.

## Memory

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

## Processor

Detection for processors is done via yaml for basic processor detection or a single script unless custom processing of data is required (as in this example).

All yaml files are located in `includes/definitions/discovery/$os.yaml`. Defining the information here is not always 
possible and is heavily reliant on vendors being sensible with the MIBs they generate. Only snmp walks are supported 
and you must provide a sane table that can be traversed and contains all of the data you need. We will use junos-mss as 
an example here.

`includes/definitions/discovery/junos-mss.yaml`

```yaml
mib: TRAPEZE-NETWORKS-SYSTEM-MIB
modules:
    processors:
        data:
            -
                oid: trpzSysCpuInstantLoad
                num_oid: .1.3.6.1.4.1.14525.4.8.1.1.11.1.
                descr: 'Processor'
```

At the top you can define one or more mibs to be used in the lookup of data:

`mib: TRAPEZE-NETWORKS-SYSTEM-MIB`

For `data:` you have the following options:

  - `oid` (required): This is the name of the table you want to do the snmp walk on.
  - `value` (optional): This is the key within the table that contains the value. If not provided will use `oid`
  - `num_oid` (required): This is the numerical OID that contains `value`. This should always be without the appended `index`.
  - `divisor` (optional): This is the divisor to use against the returned `value`.
  - `multiplier` (optional): This is the multiplier to use against the returned `value`.
  - `descr` (required): The visible label for this sensor. It can be a key with in the table or a static string, optionally using `{{ index }}`
  - `index` (optional): This is the index value we use to uniquely identify this sensor. `{{ $index }}` will be replaced by the `index` from the snmp walk.
  - `skip_values` (optional): This is an array of values we should skip over.
  - `skip_value_lt` (optional): If sensor value is less than this, skip the discovery.
  - `skip_value_gt` (optional): If sensor value is greater than this, skip the discovery.

For `options:` you have the following available:

  - `divisor`: This is the divisor to use against the returned `value`.
  - `multiplier`: This is the multiplier to use against the returned `value`.
  - `skip_values`: This is an array of values we should skip over.
  - `skip_value_lt`: If sensor value is less than this, skip the discovery.
  - `skip_value_gt`: If sensor value is greater than this, skip the discovery.

If you aren't able to use yaml to perform the processor discovery, you will most likely need to use Advanced processor discovery:

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
