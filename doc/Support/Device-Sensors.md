source: Support/Device-Sensors.md
path: blob/master/doc/

# Device Sensors

LibreNMS has a standard for device sensors they are split into
categories. This doc is to help users understand device sensors in
general, if you need help with developing sensors for a device please
see the Contributing + Developing section.

## Health Sensors

The High and Low values of these sensors can be edited in Web UI by
going to the device settings -> Health. There you can set your own
custom High and Low values. List of these sensors can be found here
[Link](../Developing/os/Health-Information.md)

**Note** Some values are defined by the manufactures and others are
auto calculated when you add the device into librenms. Keep in mind
every environment is different and may require user input.

## Wireless Sensors

Some Wireless have  High and Low values of these sensors can be edited
in Web UI by going to the device settings -> Wireless Sensors There
you can set your own custom High and Low values. List of these sensors
can be found here [Link](../Developing/os/Wireless-Sensors.md)

**Note** Some values are defined by the manufactures and others are
auto calculated when you add the device into librenms. Keep in mind
every environment is different and may require user input.

## State Sensors

Return states of device entries sensors states. For example.

Drive Status, Memory Status, Power Supply Status.

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

**Remember** you can set these limits inside device settings in the Web UI.

**State Sensor Critical:** Will alert on any state that returns critical = 2

**State Sensor Warning:** Will alert on any state that returns warning = 1

**Wireless Sensor Over Limit Alert Rule:** Will Alert on sensors that
listed in device settings under Wireless.

**Wireless Sensor Under Limit Alert Rule:** Will Alert on sensors that
listed in device settings under Wireless.
