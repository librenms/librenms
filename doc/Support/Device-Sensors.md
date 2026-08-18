# Device Sensors

LibreNMS has a standard for device sensors. The sensors are in
categories. This document gives a general description of the device
sensors. For the development of sensors for a device, read the
[Contributing and Developing
section](../Developing/os/Health-Information.md).

## Health Sensors

You can edit the high value and the low value of these sensors in the
web interface. Go to the device settings, then to Health. There you can
set your own high value and low value. The [health
information](../Developing/os/Health-Information.md) page lists these
sensors.

!!! note
    The manufacturer defines some values. LibreNMS calculates other
    values when you add the device. Each environment is different and
    can need a change by the user.

## Wireless Sensors

Some wireless devices have a high value and a low value for their
sensors. You can edit these values in the web interface. Go to the
device settings, then to Wireless Sensors. The [wireless
sensors](../Developing/os/Wireless-Sensors.md) page lists these
sensors.

!!! note
    The manufacturer defines some values. LibreNMS calculates other
    values when you add the device. Each environment is different and
    can need a change by the user.

## State Sensors

A state sensor records the state of a health sensor. You can use this
state for alerting. Examples are:

- Drive Status
- Memory Status
- Power Supply Status

LibreNMS maps the sensor to one of these states:

```
0 = OK
1 = Warning
2 = Critical
3 = Unknown
```

## Alerting Sensors

The Alert Rules Collection holds these alert rules. The rules below are
the default rules. The collection holds more device-specific rules.

**Sensor Over Limit Alert Rule:** it alerts on a sensor value that is
more than the limit.

**Sensor Under Limit Alert Rule:** it alerts on a sensor value that is
less than the limit.

!!! note
    You can set these limits in the device settings in the web
    interface.

**State Sensor Critical:** it alerts on a state that returns critical = 2.

**State Sensor Warning:** it alerts on a state that returns warning = 1.

**Wireless Sensor Over Limit Alert Rule:** it alerts on the sensors in
the device settings under Wireless.

**Wireless Sensor Under Limit Alert Rule:** it alerts on the sensors in
the device settings under Wireless.
