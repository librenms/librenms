source: Developing/os/Health-Information.md
path: blob/master/doc/

#### Sensors

This document will guide you through adding health / sensor information for your new device.
 
Currently we have support for the following health metrics along with the values we expect to see the data in:

| Class                           | Measurement                 |
| ------------------------------- | --------------------------- |
| airflow                         | cfm                         |
| ber                             | ratio                       |
| charge                          | %                           |
| chromatic_disperision           | ps/nm                       |
| cooling                         | W                           |
| current                         | A                           |
| dbm                             | dBm                         |
| delay                           | s                           |
| eer                             | eer                         |
| fanspeed                        | rpm                         |
| frequency                       | Hz                          |
| humidity                        | %                           |
| load                            | %                           |
| power                           | W                           |
| pressure                        | kPa                         |
| quality_factor                  | dB                          |
| runtime                         | Min                         |
| signal                          | dBm                         |
| snr                             | SNR                         |
| state                           | #                           |
| temperature                     | C                           |
| voltage                         | V                           |
| waterflow                       | l/m                         |

#### Simple health discovery

We have support for defining health / sensor discovery using YAML files so that you don't need to know how to write PHP.

> Please note that DISPLAY-HINTS are disabled so ensure you use the correct divisor / multiplier if applicable.

All yaml files are located in `includes/definitions/discovery/$os.yaml`. Defining the information here is not always 
possible and is heavily reliant on vendors being sensible with the MIBs they generate. Only snmp walks are supported 
and you must provide a sane table that can be traversed and contains all of the data you need. We will use netbotz as 
an example here.

`includes/definitions/discovery/netbotz.yaml`

```yaml
mib: NETBOTZV2-MIB
modules:
    sensors:
        airflow:
            options:
                skip_values_lt: 0
            data:
                -
                    oid: airFlowSensorTable
                    value: airFlowSensorValue
                    divisor: 10
                    num_oid: '.1.3.6.1.4.1.5528.100.4.1.5.1.2.{{ $index }}'
                    descr: airFlowSensorLabel
                    index: 'airFlowSensorValue.{{ $index }}'
```

At the top you can define one or more mibs to be used in the lookup of data:

`mib: NETBOTZV2-MIB`

For `data:` you have the following options:

The only sensor we have defined here is airflow. The available options are as follows:

  - `oid` (required): This is the name of the table you want to do the snmp walk on.
  - `value` (optional): This is the key within the table that contains the value. If not provided will use `oid`
  - `num_oid` (required): This is the numerical OID that contains `value`. This should always be without the appended `index`.
  - `divisor` (optional): This is the divisor to use against the returned `value`.
  - `multiplier` (optional): This is the multiplier to use against the returned `value`.
  - `low_limit` (optional): This is the critical low threshold that `value` should be (used in alerting). If an OID is specified then divisor / multiplier are used.
  - `low_warn_limit` (optional): This is the warning low threshold that `value` should be (used in alerting). If an OID is specified then divisor / multiplier are used.
  - `warn_limit` (optional): This is the warning high threshold that `value` should be (used in alerting). If an OID is specified then divisor / multiplier are used.
  - `high_limit` (optional): This is the critical high threshold that `value` should be (used in alerting). If an OID is specified then divisor / multiplier are used.
  - `descr` (required): The visible label for this sensor. It can be a key with in the table or a static string, optionally using `{{ index }}`
  - `index` (optional): This is the index value we use to uniquely identify this sensor. `{{ $index }}` will be replaced by the `index` from the snmp walk.
  - `skip_values` (optional): This is an array of values we should skip over (see note below).
  - `skip_value_lt` (optional): If sensor value is less than this, skip the discovery.
  - `skip_value_gt` (optional): If sensor value is greater than this, skip the discovery.
  - `entPhysicalIndex` (optional): If the sensor belongs to a physical entity then you can specify the index here.
  - `entPhysicalIndex_measured` (optional): If the sensor belongs to a physical entity then you can specify the entity type here.
  - `user_func` (optional): You can provide a function name for the sensors value to be processed through (i.e. Convert fahrenheit to celsius use `fahrenheit_to_celsius`)

For `options:` you have the following available:

  - `divisor`: This is the divisor to use against the returned `value`.
  - `multiplier`: This is the multiplier to use against the returned `value`.
  - `skip_values`: This is an array of values we should skip over (see note below).
  - `skip_value_lt`: If sensor value is less than this, skip the discovery.
  - `skip_value_gt`: If sensor value is greater than this, skip the discovery.

> `skip_values` can also compare items within the OID table against values. One example of this is:

```yaml
                    skip_values:
                    -
                      oid: sensUnit
                      op: '!='
                      value: 4
```

If you aren't able to use yaml to perform the sensor discovery, you will most likely need to use Advanced health discovery. 

#### Advanced health discovery

If you can't use the yaml files as above, then you will need to create the discovery code in php.

The directory structure for sensor information is `includes/discovery/sensors/$class/$os.inc.php`. The format of all 
of the sensors follows the same code format which is to call the `discover_sensor()` function - with the 
exception of state which requires additional code.

`discover_sensor()` Accepts the following arguments:

  - &$valid = This is always $valid['sensor'], do not pass any other values.
  - $class = Required. This is the sensor class from the table above (i.e humidity).
  - $device = Required. This is the $device array.
  - $oid = Required. This must be the numerical OID for where the data can be found, i.e .1.2.3.4.5.6.7.0
  - $index = Required. This must be unique for this sensor class, device and type.
  Typically it's the index from the table being walked or it could be the name of the OID if it's a single value.
  - $type = Required. This should be the OS name, i.e pulse.
  - $descr = Required. This is a descriptive value for the sensor. Some devices will provide names to use.
  - $divisor = Defaults to 1. This is used to divided the returned value.
  - $multiplier = Defaults to 1. This is used to multiply the returned value.
  - $low_limit = Defaults to null. Sets the low threshold limit for the sensor, used in alerting to report out range sensors.
  - $low_warn_limit = Defaults to null. Sets the low warning limit for the sensor, used in alerting to report near out of range sensors.
  - $warn_limit = Defaults to null. Sets the high warning limit for the sensor, used in alerting to report near out of range sensors.
  - $high_limit = Defaults to null. Sets the high limit for the sensor, used in alerting to report out range sensors.
  - $current = Defaults to null. Can be used to set the current value on discovery. Poller will update this on the next poll cycle anyway.
  - $poller_type = Defaults to snmp. Things like the unix-agent can set different values but for the most part this should be left as snmp.
  - $entPhysicalIndex = Defaults to null. Sets the entPhysicalIndex to be used to look up further hardware if available.
  - $entPhysicalIndex_measured = Defaults to null. Sets the type of entPhysicalIndex used, i.e ports.

For the majority of devices, this is all that's required to add support for a sensor. Polling is done based on the data gathered using `discover_sensor()`.
If custom polling is needed then the file format is similar to discovery: `includes/polling/sensors/$class/$os.inc.php`. Whilst it's possible to perform additional 
snmp queries within polling this should be avoided where possible. The value for the OID is already available as `$sensor_value`.

Graphing is performed automatically for sensors, no custom graphing is required or supported.
