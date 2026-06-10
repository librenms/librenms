# Device Sensors

LibreNMS has a standard for device sensors they are split into
categories. This doc is to help users understand device sensors in
general, if you need help with developing sensors for a device please
see the [Contributing + Developing section](../Developing/os/Health-Information.md).

## Health Sensors

The High and Low values of these sensors can be edited in Web UI by
going to the device settings -> Health. There you can set your own
custom High and Low values. List of these sensors can be found
[here](../Developing/os/Health-Information.md)

!!! note
    Some values are defined by the manufactures and others are
    auto calculated when you add the device into librenms. Keep in mind
    every environment is different and may require user input.

## Wireless Sensors

Some Wireless devices have High and Low values for sensors and can be
edited in Web UI by going to the device settings -> Wireless Sensors
There you can set your own custom High and Low values. List of these
sensors can be found [here](../Developing/os/Wireless-Sensors.md)

!!! note
    Some values are defined by the manufactures and others are
    auto calculated when you add the device into librenms. Keep in mind
    every environment is different and may require user input.

## State Sensors

Records the state of some health sensor which can be used for alerting. For example:

- Drive Status
- Memory Status
- Power Supply Status

And will provide a mapped state to one of the following:

```
0 = OK
1 = Warning
2 = Critical
3 = Unknown
```

## Alerting Sensors

These alert rules can be found inside the Alert Rules Collection. The
alert rules below are the default alert rules, there are more
device-specific alert rules in the alerts collection.

**Sensor Over Limit Alert Rule:**  Will alert on any sensor value that
is over the limit.

**Sensor Under Limit Alert Rule:** Will alert on any sensor value that
is under the limit.

!!! note
    You can set these limits inside device settings in the Web UI.

**State Sensor Critical:** Will alert on any state that returns critical = 2

**State Sensor Warning:** Will alert on any state that returns warning = 1

**Wireless Sensor Over Limit Alert Rule:** Will Alert on sensors that
listed in device settings under Wireless.

**Wireless Sensor Under Limit Alert Rule:** Will Alert on sensors that
listed in device settings under Wireless.
