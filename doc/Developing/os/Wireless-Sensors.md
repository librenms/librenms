source: Developing/os/Wireless-Sensors.md

This document will guide you through adding wireless sensors for your new wireless device.
 
Currently we have support for the following wireless metrics along with the values we expect to see the data in:

| Type                            | Measurement                 | Interface                     |
| ------------------------------- | --------------------------- | ----------------------------- |
| ccq                             | %                           | WirelessCcqDiscovery          |
| clients                         | count                       | WirelessClientsDiscovery      |
| noise-floor                     | dBm/Hz                      | WirelessNoiseFloorDiscovery   |

You will need to create a new OS class for your os if one doen't exist under `LibreNMS/OS`.  The name of this file
should be the os name in camel case for example `airos -> Airos`, `ios-wlc -> IosWlc`.


Your new OS class should extend LibreNMS\OS and implement the interfaces for the sensors your os supports.
```php
namespace LibreNMS\OS;

use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\OS;

class Airos extends OS implements WirelessClientsDiscovery
{
    public functon discoverWirelessClients()
    {
        $oid = '.1.3.6.1.4.1.41112.1.4.5.1.15.1'; //UBNT-AirMAX-MIB::ubntWlStatStaCount.1
        return array(
            new WirelessSensor('clients', $this->getDeviceId(), $oid, 'airos', 1, 'Clients')
        );
    }
}
```

All discovery interfaces will require you to return an array of WirelessSensor objects.

`new WirelessSensor()` Accepts the following arguments:

  - $type = Required. This is the sensor class from the table above (i.e humidity).
  - $device_id = Required. You can get this value with $this->getDeviceId()
  - $oids = Required. This must be the numerical OID for where the data can be found, i.e .1.2.3.4.5.6.7.0.
  If this is an array of oids, you should probably specify an $aggregator.
  - $index = Required. This must be unique for this sensor type, device and subtype.
  Typically it's the index from the table being walked or it could be the name of the OID if it's a single value.
  - $subtype = Required. This should be the OS name, i.e airos.
  - $description = Required. This is a descriptive value for the sensor.
  Shown to the user, if this is a per-ssid statistic, using `SSID: $ssid` here is appropriate
  - $current = Defaults to null. Can be used to set the current value on discovery.
  If this is null the values will be polled right away and if they do not return valid value(s), the sensor will not be discovered.
  Supplying a value here implies you have already verified this sensor is valid.
  - $divisor = Defaults to 1. This is used to divided the returned value.
  - $multiplier = Defaults to 1. This is used to multiply the returned value.
  - $aggregator = Defaults to sum. Valid values: sum, avg. This will combine multiple values from multiple oids into one.
  - $access_point_id = Defaults to null. If this is a wireless controller, you can link sensors to entries in the access_points table.
  - $high_limit = Defaults to null. Sets the high limit for the sensor, used in alerting to report out range sensors.
  - $low_limit = Defaults to null. Sets the low threshold limit for the sensor, used in alerting to report out range sensors.
  - $high_warn = Defaults to null. Sets the high warning limit for the sensor, used in alerting to report near out of range sensors.
  - $low_warn = Defaults to null. Sets the low warning limit for the sensor, used in alerting to report near out of range sensors.
  - $entPhysicalIndex = Defaults to null. Sets the entPhysicalIndex to be used to look up further hardware if available.
  - $entPhysicalIndexMeasured = Defaults to null. Sets the type of entPhysicalIndex used, i.e ports.

Polling is done automatically based on the discovered data.  If for some reason you need to override polling, you can implement 
the required polling interface in `LibreNMS/Interfaces/Polling/Sensors`.  Using the polling interfaces should be avoided if possible.

Graphing is performed automatically for wireless sensors, no custom graphing is required or supported.
