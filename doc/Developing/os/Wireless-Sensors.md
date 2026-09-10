This document describes how to add wireless sensors for your new
wireless device.

LibreNMS supports these wireless metrics. The table gives the expected
unit of each value:

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

If `LibreNMS/OS` holds no class for your OS, create one. Give the file
the OS name in camel case. Two examples are `airos -> Airos` and
`ios-wlc -> IosWlc`.

Your new OS class extends `LibreNMS\OS`. It implements the interfaces
of the sensors of your OS.

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

Each discovery interface returns an array of WirelessSensor objects.

`new WirelessSensor()` accepts these arguments:

- `$type =` required. The sensor class from the table above, such as humidity.
- `$device_id =` required. `$this->getDeviceId()` gives this value.
- `$oids =` required. The numeric OID of the data, such as
  .1.2.3.4.5.6.7.0. For an array of OIDs, also give an `$aggregator`.
- `$subtype =` required. The OS name, such as airos.
- `$index =` required. It must be unique for this sensor type, device,
  and subtype. It is usually the index of the walked table. For a
  single value, it can be the name of the OID.
- `$description =` required. A description of the sensor for the user.
  For a statistic of one SSID, use the form `SSID: $ssid`
- `$current =` the default is null. It sets the current value at the
  discovery. With null, LibreNMS polls the values immediately. Without
  a valid value, LibreNMS does not discover the sensor. A value here
  means that you already tested the sensor.
- `$multiplier =` the default is 1. It multiplies the returned value.
- `$divisor =` the default is 1. It divides the returned value.
- $aggregator = the default is sum. The valid values are sum and avg.
  It combines the values of several OIDs into one value.
- `$access_point_id =` the default is null. On a wireless controller,
  it links a sensor to an entry in the access_points table.
- `$high_limit =` the default is null. It sets the high limit of the
  sensor. The alerting uses it to report a sensor out of range.
- `$low_limit =` the default is null. It sets the low threshold of the
  sensor. The alerting uses it to report a sensor out of range.
- `$high_warn =` the default is null. It sets the high warning limit of
  the sensor. The alerting uses it to report a sensor near its range
  limit.
- `$low_warn =` the default is null. It sets the low warning limit of
  the sensor. The alerting uses it to report a sensor near its range
  limit.
- `$entPhysicalIndex =` the default is null. It sets the
  entPhysicalIndex for the lookup of more hardware.
- `$entPhysicalIndexMeasured =` the default is null. It sets the type
  of the entPhysicalIndex, such as ports.

LibreNMS polls automatically from the discovered data. To override the
polling, implement the necessary polling interface in
`LibreNMS/Interfaces/Polling/Sensors`. Avoid the polling interfaces
where possible.

LibreNMS graphs the wireless sensors automatically. Your own graphing
code is not necessary and not supported.
