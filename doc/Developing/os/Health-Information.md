# Health and Sensor Information

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
You can use scalar values in the sensors module instead of only tables.
For SNMP tables, give the table in `oid:` and the column in `value:`.
For scalar sensors, give the scalar OID in `oid:` and omit `value:`.

The example below shows a table-based sensor for netbotz:

`resources/definitions/os_discovery/netbotz.yaml`

```yaml
modules:
    sensors:
        airflow:
            data:
                -
                    oid: NETBOTZV2-MIB::airFlowSensorTable
                    value: NETBOTZV2-MIB::airFlowSensorValue
                    skip_value_lt: 0
                    divisor: 10
                    num_oid: '.1.3.6.1.4.1.5528.100.4.1.5.1.2.{{ $index }}'
                    descr: '{{ NETBOTZV2-MIB::airFlowSensorLabel }}'
                    index: 'airFlowSensorValue.{{ $index }}'
```

The example below shows a scalar sensor:

```yaml
modules:
    sensors:
        temperature:
            data:
                -
                    oid: ACME-SYSTEM-MIB::acmeChassisTemp.0
                    num_oid: '.1.3.6.1.4.1.99999.3.2.0'
                    descr: 'Chassis Temperature'
```

Always prefer textual OIDs over numeric OIDs in discovery files. The
sensors module only supports textual OIDs for `oid:` and `value:`. Never
use numeric OIDs for `oid:` or `value:`.

Avoid using `options:` at the class level. Define settings explicitly on each
sensor entry under `data:`. It is better to be explicit.

`data:` accepts these options:

This example defines only the airflow sensor. These options are
available:

- `oid` (required): the name of the table for the SNMP walk, with the
  MIB name in front. An example is `NETBOTZV2-MIB::airFlowSensorTable`.
  Do not use `*Entry` names; use `*Table` instead. For scalar sensors,
  provide the scalar OID. The sensors module only supports textual OIDs.
- `value` (optional): the key of the value in the table, with the MIB
  name in front. An example is `NETBOTZV2-MIB::airFlowSensorValue`.
  If the sensors use an SNMP table, `oid` must be the table, and `value`
  must be the column in that table. Omit this option if `value` matches
  `oid` (such as for scalar sensors). Without this option, LibreNMS uses `oid`.
  The sensors module only supports textual OIDs.
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
- `category` (optional): the category of the sensor: `stack` for stack
  and virtual chassis member sensors. On the device Overview page,
  sensors of any class in the same category share one panel, and
  `group` still groups them inside it. Sensors without a category show
  in the panel of their sensor class.
- `index` (optional): the unique index value of this sensor. Omit
  this key unless auto-generated indexes collide. LibreNMS uses the row
  index from the SNMP walk automatically. If multiple tables in the same
  sensor class produce the same index values, add a prefix (for
  example, `index: 'inlet.{{ $index }}'`). When you use `skip_values` to
  provide alternative definitions for different models or firmware
  versions, identical indexes are desired.
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
- `user_func` (optional): converts the sensor value, after the divisor
  and multiplier. Use a method of `LibreNMS\Util\UserFuncHelper`, such as
  `fahrenheit_to_celsius`, `kelvin_to_celsius`, `mw_to_dbm`, `uw_to_dbm`,
  `string_to_float`, `ieee754_to_decimal`, `dateToRuntime`,
  `hhmmssToMinutes` or `highestBit`. A static method of the OS class, such
  as `\LibreNMS\OS\Pmp::cnMaestroConnectionStatus`, gets the value, the
  raw value and the sensor.
- `snmp_flags` (optional): the flags for snmpwalk. They override the
  flags of the sensor type and of the OS. The default is `'-OQUb'`.
  String indexes are a common problem. The value `'-OQUsbe'` converts
  them to numeric OIDs. The value `['-OQUsbe', '-Pu']` also permits `_`
  in an OID name. For more information, read the [man
  page](https://linux.die.net/man/1/snmpcmd)
- `rrd_type` (optional): the type of the new RRD file for the data. The
  default type is GAUGE. For more details, read:
  https://oss.oetiker.ch/rrdtool/doc/rrdcreate.en.html

Avoid using `options:` to set shared values across entries. Set these keys
directly inside each item under `data:` instead. It is better to be explicit.

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
position is 0. LibreNMS walks the referenced OID when it includes the MIB
name, as in the examples below. Other OIDs must be walked by the yaml,
for example in `additional_oids`.

`{{ IF-MIB::ifName:2 }}`

This example uses the 3rd index value of the current table. The first
position is 0. It gets the IF-MIB::ifName value from the data.

You can also give several index values as a range or as a list of index
positions.

Range: `{{ IP-MIB::ipAddressPrefixOrigin:0-3 }}`
List: `{{ IP-MIB::ipAddressPrefixOrigin:2.3.1.4 }}`

#### Skipping rows of the returned data

Generally let discovery probe walks to see if the device supports that
OID. If an OID table is not supported or returns empty, LibreNMS skips
it automatically.

Use `skip_values` to filter the returned rows and discover only the valid
sensors. This filter helps when a device returns all possible sensors or
mixes sensor types in one table.

When you use `skip_values` with the same index, use it to define
alternative definitions of the exact same sensor (for example, different
units, multipliers, or state mappings), rather than to support
different hardware models.

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

If the YAML files above do not work, write the discovery code in the
PHP class of the OS, `LibreNMS/OS/$Os.php`. Use YAML where YAML is
possible. We usually reject PHP discovery in that case, because PHP
causes more problems later.

The sensors module gets the sensors from `discoverSensors()` of the OS.
It discovers, in this order:

1. ENTITY-SENSOR-MIB sensors (`discoverEntitySensors()`).
   Shared\Cisco adds CISCO-ENTITY-SENSOR-MIB sensors, they replace the
   ENTITY-SENSOR-MIB sensors of the same entity.
2. The sensors of the OS code, `discoverOsSensors()`. OS that run on
   servers (Unix, Windows, VMware ESXi) use the `ServerHardware` trait,
   it adds the sensors of the Dell OpenManage, HP Insight or Supermicro
   SuperDoctor agent that answers.
3. UPS-MIB sensors when the OS has the `rfc1628_compat` setting.
4. The YAML sensors.

A sensor replaces earlier sensors of the same class, type and index.

To add sensors, override `discoverOsSensors()` and return a Collection
of `App\Models\Sensor` models. Include the sensors of the parent class,
the OS may extend a group class with sensors of its own. The
attributes of a sensor are:

- `sensor_class` = required. The sensor class from the table above, such
  as humidity.
- `sensor_oid` = required. The numeric OID of the data, such as
  .1.2.3.4.5.6.7.0.
- `sensor_index` = required. It must be unique for this sensor class,
  device, and type. It is usually the index of the walked table. For a
  single value, it can be the name of the OID.
- `sensor_type` = required. The OS name, such as pulse. For a state
  sensor, it is the state name.
- `sensor_descr` = required. A description of the sensor. Some devices
  supply a name.
- `poller_type` = required, `snmp`.
- `sensor_divisor` and `sensor_multiplier` = the default is 1. They
  convert the polled value.
- `sensor_current` = the current value at the discovery, converted like
  the poller converts it. The poller updates this value at the next
  poll cycle.
- `sensor_limit_low`, `sensor_limit_low_warn`, `sensor_limit_warn` and
  `sensor_limit` = the default is null. The low limit, the low warning
  limit, the high warning limit and the high limit. The alerting uses
  them to report a sensor out of range.
- `entPhysicalIndex` and `entPhysicalIndex_measured` = the default is
  null. They link the sensor to an entity or a port, see the YAML
  fields above.
- `user_func` = the default is null. See the YAML field above.
- `group` = the default is null. It groups the sensors in the web
  interface under this text.
- `category` = the default is null. A `SensorCategory` case, see
  `category` above.
- `rrd_type` = the default is 'GAUGE'. It changes the type of the new
  RRD file of this sensor. For more details, read the RRD
  documentation: https://oss.oetiker.ch/rrdtool/doc/rrdcreate.en.html

A state sensor gets its state translations with `withStates()`, see
[Sensor State Support](../Sensor-State-Support.md).

The poller reads the `sensor_oid` of every sensor and converts the
value with the state translations, divisor, multiplier and user_func.
For values the poller cannot read like this, override `pollSensors()`
of the OS. It returns the raw values, before the divisor, multiplier and
user_func are applied, by sensor_id. More SNMP queries in the polling
are possible, but avoid them.

```php
public function pollSensors(Collection $sensors): array
{
    $values = parent::pollSensors($sensors);

    // temperatures are strings, like "Temp: 45 C"
    foreach ($sensors->where('sensor_class', 'temperature') as $sensor) {
        if (preg_match('/(-?[0-9]+(?:\.[0-9]+)?) C/', (string) ($values[$sensor->sensor_id] ?? ''), $matches)) {
            $values[$sensor->sensor_id] = $matches[1];
        }
    }

    return $values;
}
```

LibreNMS graphs the sensors automatically. Your own graphing code is
not necessary and not supported.

### Adding a new sensor class

Add code for your new sensor class to these existing files:

- `LibreNMS/Enum/SensorType.php`: add accordingly, find free icon from [Font Awesome](https://fontawesome.com/icons?d=gallery&m=free)
- `doc/Developing/os/Health-Information.md`: documentation for every sensor class is mandatory.
- `app/Models/Traits/HasThresholds.php`: optional - if sensible low and high limits
are guessable when a SNMP-retrievable threshold is not available, add a case for the sensor class
to `guessLimits()`.
- `LibreNMS/Util/ObjectCache.php`: optional - choose menu grouping for the sensor class.
- `lang/en/sensors.php`: add human-readable names and units for the sensor class
in English. You can also add other languages.

Create and populate new files for the sensor class in the following places:

- `includes/html/graphs/device/$class.inc.php`: define unit names used in RRDtool graphs.
- `includes/html/graphs/sensor/$class.inc.php`: define various [parameters](https://oss.oetiker.ch/rrdtool/doc/rrdgraph_graph.en.html) for RRDtool graphs.

#### Advanced health sensor example

This example discovers the optical power levels in dBm of the ports of
the Adva FSP150CC family of MetroE devices. The example assumes
knowledge of SNMP and MIBs.

The code walks the port table of CM-FACILITY-MIB and the optical power
statistics of CM-PERFORMANCE-MIB. It adds the code to the OS class,
`LibreNMS/OS/AdvaFsp150.php`:

```php
protected function discoverOsSensors(): Collection
{
    $sensors = parent::discoverOsSensors();
    $data = SnmpQuery::walk([
        'CM-FACILITY-MIB::cmEthernetTrafficPortTable',
        'CM-PERFORMANCE-MIB::cmEthernetTrafficPortStatsOPT',
        'CM-PERFORMANCE-MIB::cmEthernetTrafficPortStatsOPR',
    ])->valuesByIndex();

    foreach ($data as $index => $entry) {
        if (($entry['CM-FACILITY-MIB::cmEthernetTrafficPortMediaType'] ?? null) != 'fiber') {
            continue;
        }

        $tx = $data["$index.3"]['CM-PERFORMANCE-MIB::cmEthernetTrafficPortStatsOPT'] ?? null;
        $rx = $data["$index.3"]['CM-PERFORMANCE-MIB::cmEthernetTrafficPortStatsOPR'] ?? null;
        if ($rx == 0 && $tx == 0) {
            continue; // no DOM
        }

        $ifIndex = $entry['CM-FACILITY-MIB::cmEthernetTrafficPortIfIndex'] ?? 0;
        $ifName = PortCache::getByIfIndex($ifIndex, $this->getDeviceId())?->ifName;

        foreach ([
            ['Rx', '.1.3.6.1.4.1.2544.1.12.5.1.21.1.34', 'cmEthernetTrafficPortStatsOPR', $rx],
            ['Tx', '.1.3.6.1.4.1.2544.1.12.5.1.21.1.33', 'cmEthernetTrafficPortStatsOPT', $tx],
        ] as [$direction, $oid, $name, $value]) {
            $sensors->push(new Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => 'dbm',
                'sensor_oid' => "$oid.$index.3",
                'sensor_index' => "$name.$index",
                'sensor_type' => 'adva_fsp150',
                'sensor_descr' => "$ifName $direction Power",
                'sensor_current' => $value,
                'entPhysicalIndex' => $ifIndex,
                'entPhysicalIndex_measured' => 'ports',
            ]));
        }
    }

    return $sensors;
}
```

The code loops through the index value of each port. On an Adva
device, the port names are Ethernet 1-1-1-1, 1-1-1-2, and so on. The
MIB indexes them as oid.1.1.1.1, oid.1.1.1.2, and so on.

Copper media give no optical reading, the code skips a port without
the fiber media type. Some SFPs do not collect digital optical
monitoring (DOM) data. On an Adva device without DOM, both values are 0.
0 is a valid optical power value, but two values of 0 with DOM are very
improbable. Other vendors handle optics without DOM in a different way.
Read the MIBs of your vendor.

`entPhysicalIndex` is the ifIndex of the port and
`entPhysicalIndex_measured` is `ports`. The sensor then belongs to the
port. The sensor graphs then also appear on the page of that port, and
not only on the Health page.

To test the code, run the discovery manually with
`lnms device:discover $device_id -m sensors`. The flag `-vv` shows the
debug output. The flag `-vvv` shows the unredacted debug output. 

The output under `#### Load discovery module sensors ####` holds a list of the
sensor types. A `+` means a new sensor. A `-` means a removed sensor. A
`.` means no change. No character means no discovery of that sensor.
The bottom of the output holds the changes to the database and to the
RRD files.

```
[librenms@nms-test ~]$ lnms device:discover -v 2 -m sensors
LibreNMS Discovery
164.113.194.250 2 adva_fsp150

#### Load discovery module core ####

>> Runtime for discovery module 'core': 0.0240 seconds with 66536 bytes
>> SNMP: [2/0.06s] MySQL: [3/0.00s] RRD: [0/0.00s]
#### Unload disco module core ####


#### Load discovery module sensors ####
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
#### Unload discovery module sensors ####

Discovered in 5.521 seconds

SNMP [18/3.96s]: Get[8/0.81s] Getnext[0/0.00s] Walk[10/3.15s]
MySQL [41/0.03s]: Cell[10/0.01s] Row[-4/-0.00s] Rows[31/0.02s] Column[0/0.00s] Update[2/0.00s] Insert[2/0.00s] Delete[0/0.00s]
RRD [0/0.00s]: Update[0/0.00s] Create [0/0.00s] Other[0/0.00s]
```
