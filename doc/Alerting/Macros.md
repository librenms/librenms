source: Alerting/Macros.md

# <a name="macros">Macros</a>

Macros are shorthands to either portion of rules or pure SQL enhanced with placeholders.

You can define your own macros in your `config.php`.

Example macro-implementation of Debian-Devices
```php
$config['alert']['macros']['rule']['is_debian'] = '%devices.features ~ "@debian@"';
```
And in the Rule:
```
...  && %macros.is_debian = "1" && ...
```

This Example-macro is a Boolean-macro, it applies a form of filter to the set of results defined by the rule.
All macros that are not unary should return Boolean.

You can only apply _Equal_ or _Not-Equal_ Operations on Boolean-macros where `True` is represented by `"1"` and `False` by `"0"`.

Example 
```php
((%ports.ifInOctets_rate*8) / %ports.ifSpeed)*100
```

## <a name="macros-device">Device</a> (Boolean)

Entity: `%macros.device`

Description: Only select devices that aren't deleted, ignored or disabled.

Source: `(%devices.disabled = "0" && %devices.ignore = "0")`

### <a name="macros-device-up">Device is up</a> (Boolean)

Entity: `%macros.device_up`

Description: Only select devices that are up.

Implies: %macros.device

Source: `(%devices.status = "1" && %macros.device)`

### <a name="macros-device-down">Device is down</a> (Boolean)

Entity: `%macros.device_down`

Description: Only select devices that are down.

Implies: %macros.device

Source: `(%devices.status = "0" && %macros.device)`

## <a name="macros-port">Port</a> (Boolean)

Entity: `%macros.port`

Description: Only select ports that aren't deleted, ignored or disabled.

Source: `(%ports.deleted = "0" && %ports.ignore = "0" && %ports.disabled = "0")`

### <a name="macros-port-up">Port is up</a> (Boolean)

Entity: `%macros.port_up`

Description: Only select ports that are up and also should be up.

Implies: %macros.port

Source: `(%ports.ifOperStatus = "up" && %ports.ifAdminStatus = "up" && %macros.port)`

### <a name="macros-port-down">Port is down</a> (Boolean)

Entity: `%macros.port_down`

Description: Only select ports that are down.

Implies: %macros.port

Source: `(%ports.ifOperStatus = "down" && %ports.ifAdminStatus != "down" && %macros.port)`

### <a name="macros-port-usage-perc">Port-Usage in Percent</a> (Decimal)

Entity: `%macros.port_usage_perc`

Description: Return port-usage in percent.

Source: `((%ports.ifInOctets_rate*8) / %ports.ifSpeed)*100`

## <a name="macros-time">Time</a>

### <a name="macros-time-now">Now</a> (Datetime)

Entity: `%macros.now`

Description: Alias of MySQL's NOW()

Source: `NOW()`

### <a name="macros-time-past-Nm">Past N Minutes</a> (Datetime)

Entity: `%macros.past_$m`

Description: Returns a MySQL Timestamp dated `$` Minutes in the past. `$` can only be a supported Resolution.

Example: `%macros.past_5m` is Last 5 Minutes.

Resolution: 5,10,15,30,60

Source: `DATE_SUB(NOW(),INTERVAL $ MINUTE)`

## <a name="macros-sensors">Sensors</a> (Boolean)

Entity: `%macros.sensor`

Description: Only select sensors that aren't ignored.

Source: `(%sensors.sensor_alert = 1)`

## <a name="macros-misc">Misc</a> (Boolean)

### Packet Loss

Entity: `(%macros.packet_loss_5m)`

Description: Packet loss % value for the device within the last 5 minutes.

Example: `%macros.packet_loss_5m` > 50

Entity: `(%macros.packet_loss_15m)`

Description: Packet loss % value for the device within the last 15 minutes.

Example: `%macros.packet_loss_15m` > 50

### Ports in usage perc (Int)

Entity: `((%ports.ifInOctets_rate*8)/%ports.ifSpeed)*100`

Description: Port in used more than 50%

Example: `%macros.port_in_usage_perc > 50

### Ports out usage perc (Int)

Entity: `((%ports.ifOutOctets_rate*8)/%ports.ifSpeed)*100`

Description: Port out used more than 50%

Example: `%macros.port_out_usage_perc > 50

### Ports now down (Boolean)

Entity: `%ports.ifOperStatus != %ports.ifOperStatus_prev && %ports.ifOperStatus_prev = "up" && %ports.ifAdminStatus = "up"`

Description: Ports that were previously up and have now gone down.

Example: `%macros.port_now_down = "1"`

### Device component down [JunOS]

Entity: `%sensors.sensor_class = "state" && %sensors.sensor_current != "6" && %sensors.sensor_type = "jnxFruState" && %sensors.sensor_current != "2"`

Description: Device component is down such as Fan, PSU, etc for JunOS devices.

Example: `%macros.device_component_down_junos = "1"`

### Device component down [Cisco]

Entity: `%sensors.sensor_current != "1" && %sensors.sensor_current != "5" && %sensors.sensor_type ~ "^cisco.*State$"`

Description: Device component is down such as Fan, PSU, etc for Cisco devices.

Example: `%macros.device_component_down_cisco = "1"`

### PDU over amperage [APC]

Entity: `%sensors.sensor_class = "current" && %sensors.sensor_descr = "Bank Total" && %sensors.sensor_current > %sensors.sensor_limit && %devices.os = "apc"`

Description: APC PDU over amperage

Example: `%macros.pdu_over_amperage_apc = "1"`
