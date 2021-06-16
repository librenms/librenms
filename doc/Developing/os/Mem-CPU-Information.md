source: Developing/os/Mem-CPU-Information.md
path: blob/master/doc/

This document will guide you through adding detection for Memory /
Processor for your new device.

#### Memory

LibreNMS will attempt to detect memory statistics using the standard HOST-RESOURCES-MIB and UCD-SNMP-MIB MIBs.
To detect non-standard MIBs, they can be defined via Yaml.

##### YAML

In order to successfully detect memory amount and usage, two of the for keys below are required.  Some OS only
provide a usage percentage, which will work, but a total RAM amount will not be displayed.

- total
- used
- free
- percent_used

`includes/definitions/discovery/mempools/arubaos.yaml`

```yaml
mempools:
    data:
        -
            total: WLSX-SWITCH-MIB::sysXMemorySize
            used: WLSX-SWITCH-MIB::sysXMemoryUsed
            precision: 1024
```

The code can also interpret table based OIDs and supports many of the same features as Health Sensors
including {{ }} parsing, skip_values, and precache.

Valid data entry keys:

- `oid` oid to walk to collect processor data
- `total` oid or integer total memory size in bytes (or precision)
- `used` oid memory used in bytes (or precision)
- `free` oid memory free in bytes (or precision)
- `percent_used` oid of percentage of used memory
- `descr` A visible description of the memory measurement defaults to "Memory"
- `warn_percent` Usage percentage to used for alert purposes
- `precision` precision for all byte values, typically a power of 2 (1024 for example)
- `class`used to generate rrd filename, defaults to system.  If system, buffers, and cached exist they
will be combined to calculate available memory.
- `type` used to generate rrd filename, defaults to the os name
- `index` used to generate rrd filename, defaults to the oid index
- `skip_values` skip values see [Health Sensors](Health-Information.md) for specification
- `snmp_flags` additional net-snmp flags

##### Custom Processor Discovery and Polling

If you need to implement custom discovery or polling you can implement
the MempoolsDiscovery interface and the MempoolsPolling interface in the OS class.
MempoolsPolling is optional, standard polling will be used based on OIDs stored in the database.

OS Class files reside under `LibreNMS\OS`

```php
<?php

namespace LibreNMS\OS;

use LibreNMS\Interfaces\Discovery\MempoolsDiscovery;
use LibreNMS\Interfaces\Polling\MempoolsPolling;

class Example extends \LibreNMS\OS implements MempoolsDiscovery, MempoolsPolling
{
    /**
     * Discover a Collection of Mempool models.
     * Will be keyed by mempool_type and mempool_index
     *
     * @return \Illuminate\Support\Collection \App\Models\Mempool
     */
    public function discoverMempools()
    {
        // TODO: Implement discoverMempools() method.
    }

    /**
     * @param \Illuminate\Support\Collection $mempools \App\Models\Mempool
     * @return \Illuminate\Support\Collection \App\Models\Mempool
     */
    public function pollMempools($mempools)
    {
        // TODO: Implement pollMempools() method.
    }
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
num_oid | optional | The numerical oid to fetch data from when polling, usually should be appended by {{ $index }}. Computed by discovery process if not provided.
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

##### Custom Processor Discovery and Polling

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
