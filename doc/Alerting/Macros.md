source: Alerting/Macros.md
path: blob/master/doc/

# Macros

Macros are shorthands to either portion of rules or pure SQL enhanced with placeholders.

You can define your own macros in your `config.php`.

Example macro-implementation of Debian-Devices

```php
$config['alert']['macros']['rule']['is_debian'] = 'devices.features ~ "@debian@"';
```

And in the Rule:

```
...  macros.is_debian = 1 ...
```

This Example-macro is a Boolean-macro, it applies a form of filter to
the set of results defined by the rule.

All macros that are not unary should return Boolean.

## Device (Boolean)

Entity: `macros.device`

Description: Only select devices that aren't deleted, ignored or disabled.

Source: `(devices.disabled = 0 AND devices.ignore = 0)`

### Device is up (Boolean)

Entity: `macros.device_up`

Description: Only select devices that are up.

Implies: macros.device

Source: `(devices.status = 1 AND macros.device)`

### Device is down (Boolean)

Entity: `macros.device_down`

Description: Only select devices that are down.

Implies: macros.device

Source: `(devices.status = 0 AND macros.device)`

## Port (Boolean)

Entity: `macros.port`

Description: Only select ports that aren't deleted, ignored or disabled.

Source: `(ports.deleted = 0 AND ports.ignore = 0 AND ports.disabled = 0)`

### Port is up (Boolean)

Entity: `macros.port_up`

Description: Only select ports that are up and also should be up.

Implies: macros.port

Source: `(ports.ifOperStatus = up AND ports.ifAdminStatus = up AND macros.port)`

### Port is down (Boolean)

Entity: `macros.port_down`

Description: Only select ports that are down.

Implies: macros.port

Source: `(ports.ifOperStatus = "down" AND ports.ifAdminStatus != "down" AND macros.port)`

### Port-Usage in Percent (Decimal)

Entity: `macros.port_usage_perc`

Description: Return port-usage in percent.

Source: `((ports.ifInOctets_rate*8) / ports.ifSpeed)*100`

## Time

### Now (Datetime)

Entity: `macros.now`

Description: Alias of MySQL's NOW()

Source: `NOW()`

### Past N Minutes (Datetime)

Entity: `macros.past_$m`

Description: Returns a MySQL Timestamp dated `$` Minutes in the
past. `$` can only be a supported Resolution.

Example: `macros.past_5m` is Last 5 Minutes.

Resolution: 5,10,15,30,60

Source: `DATE_SUB(NOW(),INTERVAL $ MINUTE)`

## Sensors (Boolean)

Entity: `macros.sensor`

Description: Only select sensors that aren't ignored.

Source: `(sensors.sensor_alert = 1)`

Entity: `macros.sensor_port_link = 1`

Description: Only selects sensors that have a port linked to them, the
port is up and the device is up.

Source: `(sensors.entity_link_type = "port" AND
sensors.entity_link_index = ports.ifIndex AND macros.port_up AND macros.device_up))`

## State Sensors (Boolean)

Entity: `macros.state_sensor_ok`, `macros.state_sensor_warning`,
`macros.state_sensor_critical`, `macros.state_sensor_unknown`

Description: Select state sensors by their generic status ok (0),
warning (1), critical (2), unknown (3)

Source: `(sensors.sensor_current = state_translations.state_value
AND state_translations.state_generic_value = 2)`

## Misc (Boolean)

### Packet Loss

Entity: `(macros.packet_loss_5m)`

Description: Packet loss % value for the device within the last 5 minutes.

Example: `macros.packet_loss_5m` > 50

Entity: `(macros.packet_loss_15m)`

Description: Packet loss % value for the device within the last 15 minutes.

Example: `macros.packet_loss_15m` > 50

### Ports in usage perc (Int)

Entity: `((ports.ifInOctets_rate*8)/ports.ifSpeed)*100`

Description: Port in used more than 50%

Example: `macros.port_in_usage_perc > 50

### Ports out usage perc (Int)

Entity: `((ports.ifOutOctets_rate*8)/ports.ifSpeed)*100`

Description: Port out used more than 50%

Example: `macros.port_out_usage_perc > 50

### Ports now down (Boolean)

Entity: `ports.ifOperStatus != ports.ifOperStatus_prev AND
ports.ifOperStatus_prev = "up" AND ports.ifAdminStatus = "up"`

Description: Ports that were previously up and have now gone down.

Example: `macros.port_now_down = 1`

### Port has xDP neighbour (Boolean)

Entity: `%macros.port AND %links.local_port_id = %ports.port_id`

Description: Ports that have an xDP (lldp, cdp, etc) neighbour.

Example: `macros.port_has_xdp_neighbours = 1`

### Port has xDP neighbour already known in LibreNMS (Boolean)

Entity: `%macros.port_has_neighbours AND (%links.remote_port_id IS NOT NULL)`

Description: Ports that have an xDP (lldp, cdp, etc) neighbour that is
already known in libreNMS.

Example: `macros.port_has_xdp_neighbours_device = 1`

### Device component down [JunOS]

Entity: `sensors.sensor_class = "state" AND sensors.sensor_current !=
 "6" AND sensors.sensor_type = "jnxFruState" AND sensors.sensor_current != "2"`

Description: Device component is down such as Fan, PSU, etc for JunOS devices.

Example: `macros.device_component_down_junos = 1`

### Device component down [Cisco]

Entity: `sensors.sensor_current != 1 AND sensors.sensor_current != 5
AND sensors.sensor_type ~ "^cisco.*State$"`

Description: Device component is down such as Fan, PSU, etc for Cisco devices.

Example: `macros.device_component_down_cisco = 1`

### PDU over amperage [APC]

Entity: `sensors.sensor_class = "current" AND sensors.sensor_descr =
"Bank Total" AND sensors.sensor_current > sensors.sensor_limit AND devices.os = "apc"`

Description: APC PDU over amperage

Example: `macros.pdu_over_amperage_apc = 1`
