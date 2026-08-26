## Sensors

This document describes how to add health and sensor information for
your new device.

LibreNMS supports these health metrics. The table gives the expected
unit of each value:

| Class                           | Measurement                 |
| ------------------------------- | --------------------------- |
| airflow                         | cfm                         |
| ber                             | ratio                       |
| bitrate                         | bps                         |
| charge                          | %                           |
| chromatic_dispersion            | ps/nm                       |
| cooling                         | W                           |
| count                           | #                           |
| current                         | A                           |
| dbm                             | dBm                         |
| delay                           | s                           |
| eer                             | eer                         |
| fanspeed                        | rpm                         |
| frequency                       | Hz                          |
| humidity                        | %                           |
| load                            | %                           |
| loss                            | %                           |
| percent                         | %                           |
| power                           | W                           |
| power_consumed                  | kWh                         |
| power_factor                    | ratio                       |
| pressure                        | kPa                         |
| quality_factor                  | dB                          |
| runtime                         | Min                         |
| signal                          | dBm                         |
| snr                             | SNR                         |
| state                           | #                           |
| temperature                     | C                           |
| tv_signal                       | dBmV                        |
| voltage                         | V                           |
| waterflow                       | l/m                         |
| signal_loss                     | dB                          |

### Simple health discovery

You can define the health and sensor discovery in YAML files. You
therefore do not need PHP knowledge.

> Note: DISPLAY-HINTS are disabled. Use the correct divisor and
> multiplier where necessary.

All the YAML files are in `resources/definitions/os_discovery/$os.yaml`.
This method is not always possible, because it depends on the quality
of the vendor MIBs. Only SNMP walks work. You must give a table with a
clear structure that holds all the data. The example below uses
netbotz.

`resources/definitions/os_discovery/netbotz.yaml`

```yaml
modules:
    sensors:
        airflow:
            options:
                skip_value_lt: 0
            data:
                -
                    oid: NETBOTZV2-MIB::airFlowSensorTable
                    value: NETBOTZV2-MIB::airFlowSensorValue
                    divisor: 10
                    num_oid: '.1.3.6.1.4.1.5528.100.4.1.5.1.2.{{ $index }}'
                    descr: '{{ NETBOTZV2-MIB::airFlowSensorLabel }}'
                    index: 'airFlowSensorValue.{{ $index }}'
```

Use the format MIB-NAME::OID for every OID reference.

`data:` accepts these options:

This example defines only the airflow sensor. These options are
available:

- `oid` (required): the name of the table for the SNMP walk, with the
  MIB name in front. An example is `NETBOTZV2-MIB::airFlowSensorTable`.
- `value` (optional): the key of the value in the table, with the MIB
  name in front. An example is `NETBOTZV2-MIB::airFlowSensorValue`.
  Without this option, LibreNMS uses `oid`.
- `num_oid` (required for pull requests): the numeric OID of `value`.
  Without this option, the discovery process calculates it. A pull
  request still needs this parameter. It usually holds `{{ $index }}`.
the string to the equivalent OID representation.
- `divisor` (optional): the divisor of the returned `value`.
- `multiplier` (optional): the multiplier of the returned `value`.
- `low_limit` (optional): the critical low threshold of `value`, for
  the alerting. With an OID, LibreNMS applies the divisor and the
  multiplier.
- `low_warn_limit` (optional): the warning low threshold of `value`,
  for the alerting. With an OID, LibreNMS applies the divisor and the
  multiplier.
- `warn_limit` (optional): the warning high threshold of `value`, for
  the alerting. With an OID, LibreNMS applies the divisor and the
  multiplier.
- `high_limit` (optional): the critical high threshold of `value`, for
  the alerting. With an OID, LibreNMS applies the divisor and the
  multiplier.
- `skip_limits_calc` (optional): a true or false flag. It keeps the raw
  values of the limits from an OID. With the value true, LibreNMS skips
  the divisor and the multiplier for the `_limit` values. It still
  applies `user_func` to the `_limit` values
- `descr` (required): the visible label of this sensor. It is a key in
  the table or a fixed string. It can hold `{{ index }}`.
- `group` (optional): it groups the sensors in the web interface under
  this text. Without this option, the sensors go into the default group.
  With the value `transceiver`, LibreNMS shows the sensor with the port
  and not with the generic sensors. You must also set
  `entPhysicalIndex` to ifIndex
- `index` (optional): the unique index value of this sensor. LibreNMS
  replaces `{{ $index }}` with the numeric index of this row in the SNMP
  walk table.
- `skip_values` (optional): an array of the values to skip. Read the
  note below.
- `skip_value_lt` (optional): the discovery skips a sensor value that
  is less than this value.
- `skip_value_gt` (optional): the discovery skips a sensor value that
  is more than this value.
- `entPhysicalIndex` and `entPhysicalIndex_measured` (optional): they
  link a sensor to a physical entity. These two variants are available:
    - `entPhysicalIndex` contains the entPhysicalIndex from entPhysical table, and `entPhysicalIndex_measured` is NULL
    - `entPhysicalIndex` contains "ifIndex" value of the linked port and `entPhysicalIndex_measured` contains "ports"
- `user_func` (optional): the name of a function for the sensor value.
  For example, `fahrenheit_to_celsius` converts fahrenheit to celsius
- `snmp_flags` (optional): the flags for snmpwalk. They override the
  flags of the sensor type and of the OS. The default is `'-OQUb'`.
  String indexes are a common problem. The value `'-OQUsbe'` converts
  them to numeric OIDs. The value `['-OQUsbe', '-Pu']` also permits `_`
  in an OID name. For more information, read the [man
  page](https://linux.die.net/man/1/snmpcmd)
- `rrd_type` (optional): the type of the new RRD file for the data. The
  default type is GAUGE. For more details, read:
  https://oss.oetiker.ch/rrdtool/doc/rrdcreate.en.html

`options:` accepts these values:

- `divisor`: the divisor of the returned `value`.
- `multiplier`: the multiplier of the returned `value`.
- `skip_values`: an array of the values to skip. Read the note below.
- `skip_value_lt`: the discovery skips a sensor value that is less than
  this value.
- `skip_value_gt`: the discovery skips a sensor value that is more than
  this value.

A sensor definition can hold several variables. The syntax is
`{{ MIB-NAME::variable }}`. You can use any OID of the current table
and any data from an earlier fetch. The index (`$index`) and the
subindexes are also available. A subindex exists when the OID has more
than one index. For `$index="1.20"`, `$subindex0` is "1" and
`$subindex1` is "20".

`additional_oids` gets data outside your sensor.

!!! note
    Use `additional_oids` only for data outside your sensor.

 You can also use `additional_oids` in a class. This method is the
 preferred one when only that class needs the OIDs. The example below
 shows `additional_oids` in the `temperature` class and at the
 `sensors` level.
 
!!! note
     Use only one `additional_oids` statement for the same OID. The
     example shows both positions.

```
sensors:
    additional_oids:
        data:
            -
                oid:
                    - Stulz-WIB8000-MIB::unitsettingName
    temperature:
        additional_oids:
            data:
                -
                    oid:
                        - Stulz-WIB8000-MIB::unitsettingName
        data:
            -
                oid: Stulz-WIB8000-MIB::unitTemperature
                value: Stulz-WIB8000-MIB::unitTemperature
                num_oid: '.1.3.6.1.4.1.29462.10.2.1.1.1.1.1.1.1.1170.{{ $index }}'
                index: 'unitTemperature.{{ $index }}'
                descr: 'Unit {{ Stulz-WIB8000-MIB::unitsettingName:0-1 }} temp'
                divisor: 10
            -
                oid: Stulz-WIB8000-MIB::unitSupplyAirTemperature
                value: Stulz-WIB8000-MIB::unitSupplyAirTemperature
                num_oid: '.1.3.6.1.4.1.29462.10.2.1.1.1.1.1.1.1.1193.{{ $index }}'
                index: 'unitSupplyAirTemperature.{{ $index }}'
                descr: 'Unit {{ Stulz-WIB8000-MIB::unitsettingName:0-1 }} supply temp'
                divisor: 10
```

To reach a string in an index, use `{{ $index_string }}`. You can add a
format string that gives the extraction method.
`{{ $index_string:nns }}` skips two numeric indexes and returns the
string after them.
`{{ $index_string:nss }}` skips one numeric index and one string index.
It returns the next string after them.

#### Fetching values from other tables/oids

A reference to an OID in another table uses the full index of the match.
If the two indexes do not match, give the index positions. The first
position is 0. LibreNMS must already hold the data of the other table.

`{{ IF-MIB::ifName:2 }}`

This example uses the 3rd index value of the current table. The first
position is 0. It gets the IF-MIB::ifName value from the data.

You can also give several index values as a range or as a list of index
positions.

Range: `{{ IP-MIB::ipAddressPrefixOrigin:0-3 }}`
List: `{{ IP-MIB::ipAddressPrefixOrigin:2.3.1.4 }}`

#### Skipping rows of the returned data

You can filter the returned rows and discover only the valid sensors.
This filter helps when a device returns all possible sensors or mixes
sensor types in one table.

> `skip_values` also compares the items of the OID table to values.
> LibreNMS uses the index of the sensor to get the value from the OID.
> An index at the end of the OID overrides this behaviour. You can also
> test fields from the device. A chain of comparisons uses a logical OR.
> One match is therefore enough. The discovery then skips that sensor.
> The example below shows this:

```yaml
                    skip_values:
                    -
                      oid: STE2-MIB::sensUnit
                      op: '!='
                      value: 4
                    -
                      oid: STE2-MIB::sensConfig.0
                      op: '!='
                      value: 1
                    -
                      device: STE2-MIB::hardware
                      op: 'contains'
                      value: 'rev2'
```

`op` accepts these operators:

> =, !=, ==, !==, <=, >=, <, >,
> starts, ends, contains, regex, in_array, not_starts,
> not_ends, not_contains, not_regex, not_in_array, exists

Example:

```yaml
                    skip_values:
                    -
                      oid: MIB-NAME::sensorName
                      op: 'not_in_array'
                      value: ['sensor1', 'sensor2']
```

```yaml
                    skip_values:
                    -
                      oid: MIB-NAME::sensorOptionalOID
                      op: 'exists'
                      value: false
```

```yaml
        temperature:
            additional_oids:
                data:
                    -
                        oid:
                            - ENTITY-MIB::entPhysicalName
            data:
                -
                    oid: HUAWEI-ENTITY-EXTENT-MIB::hwOpticalModuleInfoTable
                    value: HUAWEI-ENTITY-EXTENT-MIB::hwEntityOpticalTemperature
                    descr: '{{ ENTITY-MIB::entPhysicalName }}'
                    index: '{{ $index }}'
                    skip_values:
                        -
                            oid: HUAWEI-ENTITY-EXTENT-MIB::hwEntityOpticalMode
                            op: '='
                            value: '1'
```

If YAML cannot do the sensor discovery, use advanced health discovery.

### Advanced health discovery

If the YAML files above do not work, write the discovery code in PHP.
Use YAML where YAML is possible. We usually reject PHP discovery in
that case, because PHP causes more problems later.

The directory structure of the sensor information is
`includes/discovery/sensors/$class/$os.inc.php`. All sensors use the
same code format. The code collects the sensor information over SNMP.
It then calls the `discover_sensor()` function. A state sensor needs
more code. The sensor information is usually in a table in an ENTITY
MIB from the vendor of the device. Other MIB tables also work.

`discover_sensor()` accepts these arguments:

- &$valid = always null. LibreNMS does not use it.
- $class = required. The sensor class from the table above, such as humidity.
- $device = required. The `$device` array.
- $oid = required. The numeric OID of the data, such as .1.2.3.4.5.6.7.0.
- $index = required. It must be unique for this sensor class, device,
  and type. It is usually the index of the walked table. For a single
  value, it can be the name of the OID.
- $type = required. The OS name, such as pulse.
- $descr = required. A description of the sensor. Some devices supply a
  name.
- $divisor = the default is 1. It divides the returned value.
- $multiplier = the default is 1. It multiplies the returned value.
- $low_limit = the default is null. It sets the low threshold of the
  sensor. The alerting uses it to report a sensor out of range.
- $low_warn_limit = the default is null. It sets the low warning limit
  of the sensor. The alerting uses it to report a sensor near its
  range limit.
- $warn_limit = the default is null. It sets the high warning limit of
  the sensor. The alerting uses it to report a sensor near its range
  limit.
- $high_limit = the default is null. It sets the high limit of the
  sensor. The alerting uses it to report a sensor out of range.
- $current = the default is null. It sets the current value at the
  discovery. The poller updates this value at the next poll cycle.
- $poller_type = the default is snmp. A component such as the
  unix-agent sets a different value. Keep the value snmp in most cases.
- $entPhysicalIndex = the default is null. It sets the
  entPhysicalIndex for the lookup of more hardware.
- $entPhysicalIndex_measured = the default is null. It sets the type of
  the entPhysicalIndex, such as ports.
- $user_func = the default is null. It is the name of a function for
  the sensor value. For example, `fahrenheit_to_celsius` converts
  fahrenheit to celsius
- $group = the default is null. It groups the sensors in the web
  interface under this text.
- $rrd_type = the default is 'GAUGE'. It changes the type of the new
  RRD file of this sensor. For more details, read the RRD
  documentation: https://oss.oetiker.ch/rrdtool/doc/rrdcreate.en.html

For most devices, these steps give full sensor support. The polling
uses the data from `discover_sensor()`. For your own polling code, the
file format is the same as for discovery:
`includes/polling/sensors/$class/$os.inc.php`. More SNMP queries in the
polling are possible, but avoid them. The value of the OID is already
available as `$sensor_value`.

LibreNMS graphs the sensors automatically. Your own graphing code is
not necessary and not supported.

### Adding a new sensor class

Add code for your new sensor class to these existing files:

- `LibreNMS/Enum/Sensor.php`: add accordingly, find free icon from [Font Awesome](https://fontawesome.com/icons?d=gallery&m=free)
- `doc/Developing/os/Health-Information.md`: documentation for every sensor class is mandatory.
- `includes/discovery/functions.inc.php`: optional - if sensible low_limit and high_limit values
are guessable when a SNMP-retrievable threshold is not available, add a case for the sensor class
to the sensor_limit() and/or sensor_low_limit() functions.
- `LibreNMS/Util/ObjectCache.php`: optional - choose menu grouping for the sensor class.
- `includes/html/pages/device/overview.inc.php`: add `require 'overview/sensors/$class.inc.php'`
in the desired order for the device overview page.
- `lang/en/sensors.php`: add human-readable names and units for the sensor class
in English. You can also add other languages.

Create and populate new files for the sensor class in the following places:

- `includes/discovery/sensors/$class/`: create the folder where advanced php-based discovery
files are stored. Not used for yaml discovery.
=======
- `includes/html/pages/device/overview.inc.php`: add `require 'overview/sensors/$class.inc.php'` in the desired
order for the device overview page.
- `lang/en/sensors.php`: add human-readable names and units for the sensor class in English, feel
You can also add other languages.

Create and populate new files for the sensor class in the following places:

- `includes/discovery/sensors/$class/`: create the folder where advanced php-based discovery files
are stored. Not used for yaml discovery.
- `includes/html/graphs/device/$class.inc.php`: define unit names used in RRDtool graphs.
- `includes/html/graphs/sensor/$class.inc.php`: define various [parameters](https://oss.oetiker.ch/rrdtool/doc/rrdgraph_graph.en.html) for RRDtool graphs.
- `includes/html/pages/device/health/$class.inc.php`
- `includes/html/pages/device/overview/sensors/$class.inc.php`
- `includes/html/pages/health/$class.inc.php`

#### Advanced health sensor example

This example builds sensors with the advanced method. It collects the
optical power level in dBm from the Adva FSP150CC family of MetroE
devices. The example assumes knowledge of SNMP and MIBs.

The first line walks the cmEntityObject table. It gets the information
about the chassis and the line cards. From this information, the code
extracts the model type. The model type gives the tables of the ports
in the CM-Facility-Mib. The program then reads that table into the
`$data` array `adva_fsp150_ports`. This array holds the OID index of
each port. A later step uses these indexes to build the sensor OIDs.

The next step builds the sensor discovery code. These readings are
optical, so the file uses the dBm sensor type. Create the file
`includes/discover/sensors/dbm/adva_fsp150.inc.php`. This is part of
the code:

```php
$data = SnmpQuery::walk([
    'CM-FACILITY-MIB::cmEthernetTrafficPortTable',
    'CM-PERFORMANCE-MIB::cmEthernetTrafficPortStatsOPT',
    'CM-PERFORMANCE-MIB::cmEthernetTrafficPortStatsOPR',
])->valuesByIndex();

foreach ($data as $index => $entry) {
    if (isset($entry['CM-FACILITY-MIB::cmEthernetTrafficPortMediaType']) && $entry['CM-FACILITY-MIB::cmEthernetTrafficPortMediaType'] == 'fiber') {
        //Discover received power level
        $oidRx = '.1.3.6.1.4.1.2544.1.12.5.1.21.1.34.' . $index . '.3';
        $oidTx = '.1.3.6.1.4.1.2544.1.12.5.1.21.1.33.' . $index . '.3';
        $currentTx = $data[$index . '.3']['CM-PERFORMANCE-MIB::cmEthernetTrafficPortStatsOPT'] ?? null;
        $currentRx = $data[$index . '.3']['CM-PERFORMANCE-MIB::cmEthernetTrafficPortStatsOPR'] ?? null;
        if ($currentRx != 0 || $currentTx != 0) {
            $ifIndex = $entry['CM-FACILITY-MIB::cmEthernetTrafficPortIfIndex'] ?? 0;
            $ifName = PortCache::getByIfIndex($ifIndex)?->ifName;

            app('sensor-discovery')->discover(new \App\Models\Sensor([
                'poller_type' => $poller_type,
                'sensor_class' => 'dbm',
                'device_id' => $device['device_id'],
                'sensor_oid' => $oidRx,
                'sensor_index' => 'cmEthernetTrafficPortStatsOPR.' . $index,
                'sensor_type' => 'adva_fsp150,
                'sensor_descr' => $ifName . ' Rx Power',
                'sensor_divisor' => 1,
                'sensor_multiplier' => 1,
                'sensor_limit' => null,
                'sensor_limit_warn' => null,
                'sensor_limit_low' => null,
                'sensor_limit_low_warn' => null,
                'sensor_current' => $currentRx,
                'entPhysicalIndex' => $ifIndex,
                'entPhysicalIndex_measured' => 'ports',
            ]));

            app('sensor-discovery')->discover(new \App\Models\Sensor([
                'poller_type' => $poller_type,
                'sensor_class' => 'dbm',
                'device_id' => $device['device_id'],
                'sensor_oid' => $oidRx,
                'sensor_index' => 'cmEthernetTrafficPortStatsOPT.' . $index,
                'sensor_type' => 'adva_fsp150,
                'sensor_descr' => $ifName . ' Tx Power',
                'sensor_divisor' => 1,
                'sensor_multiplier' => 1,
                'sensor_limit' => null,
                'sensor_limit_warn' => null,
                'sensor_limit_low' => null,
                'sensor_limit_low_warn' => null,
                'sensor_current' => $currentTx,
                'entPhysicalIndex' => $ifIndex,
                'entPhysicalIndex_measured' => 'ports',
            ]));
        }
    }
}
```

The program first loops through the index value of each port. On an
Adva device, the port names are Ethernet 1-1-1-1, 1-1-1-2, and so on.
The MIB indexes them as oid.1.1.1.1, oid.1.1.1.2, and so on.

The program then finds the table of the port and tests the connector
type for the value 'fiber'. The full code holds other port tables. This
example leaves them out. Copper media give no optical reading. The
discovery therefore skips a port without the fiber media type.

The next two lines build the OIDs of the optical receive value and the
optical transmit value. They use the `$index` of the port. The program
then gets the current receive value and the current transmit value,
that is `$currentRx` and `$currentTx`. It tests both values against 0.
Some SFPs do not collect digital optical monitoring (DOM) data. On an
Adva device without DOM, both values are 0. 0 is a valid optical power
value, but two values of 0 with DOM are very improbable. Without DOM,
the program stops the discovery of that port. Other vendors handle
optics without DOM in a different way. Read the MIBs of your vendor.

The program then sets `$entPhysicalIndex` and
`$entPhysicalIndex_measured`. Here, `$entPhysicalIndex` takes the value
of `CM-FACILITY-MIB::cmEthernetTrafficPortIfIndex`. The sensor then
belongs to the port. The sensor graphs then also appear on the page of
that port, and not only on the Health page.

The program then reads the description of the port from the database.
This description becomes the title of the graph in the web interface.

The program then calls `discover_sensor()` with the collected
information. The `null` values are the low limit, the low warning
limit, the high limit, and the high warning limit. The Adva MIB does
not hold these values.

To test the code, run the discovery manually with
`lnms device:discover $device_id -m sensors`. The flag `-v` shows the
calls of the discovery. The flag `-d` shows the debug output. The
output under `#### Load disco module sensors ####` holds a list of the
sensor types. A `+` means a new sensor. A `-` means a removed sensor. A
`.` means no change. No character means no discovery of that sensor.
The bottom of the output holds the changes to the database and to the
RRD files.

```
[librenms@nms-test ~]$ lnms device:discover 2 -m sensors
LibreNMS Discovery
164.113.194.250 2 adva_fsp150

#### Load disco module core ####

>> Runtime for discovery module 'core': 0.0240 seconds with 66536 bytes
>> SNMP: [2/0.06s] MySQL: [3/0.00s] RRD: [0/0.00s]
#### Unload disco module core ####


#### Load disco module sensors ####
Pre-cache adva_fsp150:
 ENTITY-SENSOR: Caching OIDs: entPhysicalDescr entPhysicalName entPhySensorType entPhySensorScale entPhySensorPrecision entPhySensorValue entPhySensorOperStatus
Airflow:
Current: .
Charge:
Dbm: Adva FSP-150 dBm..
Fanspeed:
Frequency:
Humidity:
Load:
Power:
Power_consumed:
Power_factor:
Runtime:
Signal:
State:
Count:
Temperature: ..
Tv_signal:
Bitrate:
Voltage: .
Snr:
Pressure:
Cooling:
Delay:
Quality_factor:
Chromatic_dispersion:
Ber:
Eer:
Waterflow:
Percent:
Signal_loss:

>> Runtime for discovery module 'sensors': 3.9340 seconds with 190024 bytes
>> SNMP: [16/3.89s] MySQL: [36/0.03s] RRD: [0/0.00s]
#### Unload disco module sensors ####

Discovered in 5.521 seconds

SNMP [18/3.96s]: Get[8/0.81s] Getnext[0/0.00s] Walk[10/3.15s]
MySQL [41/0.03s]: Cell[10/0.01s] Row[-4/-0.00s] Rows[31/0.02s] Column[0/0.00s] Update[2/0.00s] Insert[2/0.00s] Delete[0/0.00s]
RRD [0/0.00s]: Update[0/0.00s] Create [0/0.00s] Other[0/0.00s]
```
