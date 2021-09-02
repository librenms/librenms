source: Developing/os/Wireless-Sensors.md
path: blob/master/doc/

This document will guide you through adding wireless sensors for your
new wireless device.

Currently we have support for the following wireless metrics along
with the values we expect to see the data in:

| Type        | Measurement | Interface                    | Description                                                                                     |
| ----------- | ----------- | ---------------------------- | ----------------------------------------------------------------------------------------------- |
| ap-count    | %           | WirelessApCountDiscovery     | The number of APs attached to this controller                                                   |
| capacity    | %           | WirelessCapacityDiscovery    | The % of operating rate vs theoretical max                                                      |
| ccq         | %           | WirelessCcqDiscovery         | The Client Connection Quality                                                                   |
| channel     | count       | WirelessChannelDiscovery     | The channel, use of frequency is preferred                                                      |
| cell        | count       | WirelessCellDiscovery        | The cell in a multicell technology                                                              |
| clients     | count       | WirelessClientsDiscovery     | The number of clients connected to/managed by this device                                       |
| distance    | km          | WirelessDistanceDiscovery    | The distance of a radio link in Kilometers                                                      |
| error-rate  | bps         | WirelessErrorRateDiscovery   | The rate of errored packets or bits, etc                                                        |
| error-ratio | %           | WirelessErrorRatioDiscovery  | The percent of errored packets or bits, etc                                                     |
| errors      | count       | WirelessErrorsDiscovery      | The total bits of errored packets or bits, etc                                                  |
| frequency   | MHz         | WirelessFrequencyDiscovery   | The frequency of the radio in MHz, channels can be converted                                    |
| mse         | dB          | WirelessMseDiscovery         | The Mean Square Error                                                                           |
| noise-floor | dBm         | WirelessNoiseFloorDiscovery  | The amount of noise received by the radio                                                       |
| power       | dBm         | WirelessPowerDiscovery       | The power of transmit or receive, including signal level                                        |
| quality     | %           | WirelessQualityDiscovery     | The % of quality of the link, 100% = perfect link                                               |
| rate        | bps         | WirelessRateDiscovery        | The negotiated rate of the connection (not data transfer)                                       |
| rssi        | dBm         | WirelessRssiDiscovery        | The Received Signal Strength Indicator                                                          |
| snr         | dB          | WirelessSnrDiscovery         | The Signal to Noise ratio, which is signal - noise floor                                        |
| sinr        | dB          | WirelessSinrDiscovery        | The Signal-to-Interference-plus-Noise Ratio                                                     |
| rsrq        | dB          | WirelessRsrqDiscovery        | The Reference Signal Received Quality                                                           |
| rsrp        | dBm         | WirelessRsrpDiscovery        | The Reference Signals Received Power                                                            |
| xpi         | dBm         | WirelessXpiDiscovery         | The Cross Polar Interference values                                                             |
| ssr         | dB          | WirelessSsrDiscovery         | The Signal strength ratio, the ratio(or difference) of Vertical rx power to Horizontal rx power |
| utilization | %           | WirelessUtilizationDiscovery | The % of utilization compared to the current rate                                               |

You will need to create a new OS class for your os if one doesn't exist
under `LibreNMS/OS`.  The name of this file should be the os name in
camel case for example `airos -> Airos`, `ios-wlc -> IosWlc`.

Your new OS class should extend LibreNMS\OS and implement the
interfaces for the sensors your os supports.

```php
namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\OS;

class Airos extends OS implements WirelessClientsDiscovery
{
    public function discoverWirelessClients()
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

- `$type =` Required. This is the sensor class from the table above (i.e humidity).
- `$device_id =` Required. You can get this value with $this->getDeviceId()
- `$oids =` Required. This must be the numerical OID for where the data
  can be found, i.e .1.2.3.4.5.6.7.0. If this is an array of oids, you
  should probably specify an $aggregator.
- `$subtype =` Required. This should be the OS name, i.e airos.
- `$index =` Required. This must be unique for this sensor type, device and subtype.
  Typically it's the index from the table being walked or it could be
  the name of the OID if it's a single value.
- `$description =` Required. This is a descriptive value for the sensor.
  Shown to the user, if this is a per-ssid statistic, using `SSID:
  $ssid` here is appropriate
- `$current =` Defaults to null. Can be used to set the current value on discovery.
  If this is null the values will be polled right away and if they do
  not return valid value(s), the sensor will not be
  discovered. Supplying a value here implies you have already verified
  this sensor is valid.
- `$multiplier =` Defaults to 1. This is used to multiply the returned value.
- `$divisor =` Defaults to 1. This is used to divided the returned value.
- $aggregator = Defaults to sum. Valid values: sum, avg. This will
  combine multiple values from multiple oids into one.
- `$access_point_id =` Defaults to null. If this is a wireless
  controller, you can link sensors to entries in the access_points table.
- `$high_limit =` Defaults to null. Sets the high limit for the sensor,
  used in alerting to report out range sensors.
- `$low_limit =` Defaults to null. Sets the low threshold limit for the
  sensor, used in alerting to report out range sensors.
- `$high_warn =` Defaults to null. Sets the high warning limit for the
  sensor, used in alerting to report near out of range sensors.
- `$low_warn =` Defaults to null. Sets the low warning limit for the
  sensor, used in alerting to report near out of range sensors.
- `$entPhysicalIndex =` Defaults to null. Sets the entPhysicalIndex to
  be used to look up further hardware if available.
- `$entPhysicalIndexMeasured =` Defaults to null. Sets the type of
  entPhysicalIndex used, i.e ports.

Polling is done automatically based on the discovered data.  If for
some reason you need to override polling, you can implement the
required polling interface in `LibreNMS/Interfaces/Polling/Sensors`.
Using the polling interfaces should be avoided if possible.

Graphing is performed automatically for wireless sensors, no custom
graphing is required or supported.
