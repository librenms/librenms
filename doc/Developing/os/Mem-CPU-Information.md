This document describes how to add memory detection and processor
detection for your new device.

#### Memory

LibreNMS tries to detect the memory statistics with the standard
HOST-RESOURCES-MIB and UCD-SNMP-MIB MIBs.
For a non-standard MIB, give the definition in YAML.

##### YAML

The detection of the memory amount and the memory use needs two of the
four keys below. Some operating systems supply only a use percentage.
This value works, but LibreNMS then shows no total RAM amount.

- total
- used
- free
- percent_used

`resources/definitions/os_discovery/mempools/arubaos.yaml`

```yaml
mempools:
    data:
        -
            total: WLSX-SWITCH-MIB::sysXMemorySize
            used: WLSX-SWITCH-MIB::sysXMemoryUsed
            precision: 1024
```

The code also reads table based OIDs. It supports many features of the
health sensors, such as `{{ }}` parsing, `skip_values`, and precache.

Valid data entry keys:

- `oid` the OID of the walk for the processor data
- `total` an OID or an integer with the total memory size in bytes, or
  in the precision unit
- `used` an OID with the used memory in bytes, or in the precision unit
- `free` an OID with the free memory in bytes, or in the precision unit
- `percent_used` an OID with the percentage of used memory
- `descr` a visible description of the memory measurement. The default
  is "Memory"
- `warn_percent` the use percentage of the alert
- `precision` the precision of all byte values. It is usually a power
  of 2, such as 1024
- `class` it builds the rrd filename. The default is `system`. If
  `system`, `buffers`, and `cached` exist, LibreNMS combines them to
  calculate the available memory
- `type` it builds the rrd filename. The default is the OS name
- `index` it builds the rrd filename. The default is the OID index
- `skip_values` the values to skip. For the specification, read [Health
  Sensors](Health-Information.md)
- `snmp_flags` more net-snmp flags

##### Custom Processor Discovery and Polling

For your own discovery or polling, implement the MempoolsDiscovery
interface and the MempoolsPolling interface in the OS class.
MempoolsPolling is optional. Without it, LibreNMS uses the standard
polling with the OIDs from the database.

The OS class files are in `LibreNMS\OS`.

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

A YAML file does the processor detection. Your own code is necessary
only for a special data process.

##### YAML

`resources/definitions/os_discovery/pulse.yaml`

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
oid | required | The textual OID of the data. It is a table or a single value
num_oid | optional | The numeric OID of the polling data. It usually ends with {{ $index }}. Without this key, the discovery process calculates it
value | optional | The OID of the data. It applies mainly to a table
precision | 1 | The multiplier of the data. With a negative value, LibreNMS multiplies the data and then subtracts the result from 100
descr | Processor | The description of this processor. It is an OID or a plain string. The values {{ $index }} and {{$count}} are useful
type | <os name> | The name of this sensor. With the index, it builds a unique id for this sensor
index | {{ $index }} | The index of this sensor. The default is the index of the OID
skip_values | optional | It skips this sensor at a match of the value

Accessing values within yaml:

| | |
| --- | --- |
| {{ $index }} | The index after the given OID |
| {{ $count }} | The count of the entries. It starts at 1 |
| {{ $`oid` }} | Any OID in the table or from an earlier fetch |

##### Custom Processor Discovery and Polling

For your own discovery or polling, implement the ProcessorDiscovery
interface and the ProcessorPolling interface in the OS class.

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
