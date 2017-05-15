source: Developing/os/Health-Information.md

This document will guide you through adding health / sensor information for your new device.
 
Currently we have support for the following health metrics along with the values we expect to see the data in:

| Class                           | Measurement                 |
| ------------------------------- | --------------------------- |
| airflow                         | cfm                         |
| charge                          | %                           |
| current                         | A                           |
| dbm                             | dBm                         |
| fanspeed                        | rpm                         |
| frequency                       | Hz                          |
| humidity                        | %                           |
| load                            | %                           |
| power                           | W                           |
| runtime                         | Min                         |
| signal                          | dBm                         |
| state                           | #                           |
| temperature                     | C                           |
| voltage                         | V                           |

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
