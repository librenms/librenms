source: Developing/os/Mem-CPU-Information.md
path: blob/master/doc/

This document will guide you through adding detection for Memory /
Processor for your new device.

#### Memory

Detection for memory is done via two php scripts, one for discovery
and the other for polling:

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

Detection for processors is done via a yaml file unless custom
processing of data is required.

##### YAML

`includes/definitions/discovery/pulse.yaml`

```yaml
mib: PULSESECURE-PSG-MIB
modules:
    processors:
          data:
              -
                  oid: iveCpuUtil
                  num_oid: '.1.3.6.1.4.1.12532.10.{{ $index }}'
                  type: pulse
```

Available yaml data keys:

Key | Default | Description
----- | --- | -----
oid | required | The string based oid to fetch data, could be a table or a single value
num_oid | required | the numerical oid to fetch data from when polling, usually should be appended by {{ $index }}
value | optional | Oid to retrieve data from, primarily used for tables
precision | 1 | The multiplier to multiply the data by. If this is negative, the data will be multiplied then subtracted from 100.
descr | Processor | Description of this processor, may be an oid or plain string.  Helpful values {{ $index }} and {{$count}}
type | <os name> | Name of this sensor. This is used with the index to generate a unique id for this sensor.
index | {{ $index }} | The index of this sensor, defaults to the index of the oid.
skip_values | optional | Do not detect this sensor if the value matches

Accessing values within yaml:

| | |
| --- | --- |
| {{ $index }} | The index after the given oid |
| {{ $count }} | The count of entries (starting with 1) |
| {{ $`oid` }} | Any oid in the table or pre-fetched |

##### Custom Discovery and Polling

If you need to implement custom discovery or polling you can implement
the ProcessorDiscovery interface and the ProcessorPolling interface in the OS class.

OS Class files reside under `LibreNMS\OS`

```php
<?php
namespace LibreNMS\OS;

use LibreNMS\Device\Processor;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\Interfaces\Polling\ProcessorPolling;
use LibreNMS\OS;

class ExampleOS extends OS implements ProcessorDiscovery, ProcessorPolling
{
    /**
     * Discover processors.
     * Returns an array of LibreNMS\Device\Processor objects that have been discovered
     *
     * @return array Processors
     */
    public function discoverProcessors()
    {
        // discovery code here
    }

    /**
     * Poll processor data.  This can be implemented if custom polling is needed.
     *
     * @param array $processors Array of processor entries from the database that need to be polled
     * @return array of polled data
     */
    public function pollProcessors(array $processors)
    {
        // polling code here
    }
}
```
