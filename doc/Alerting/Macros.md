# Macros

A macro is a short form of a part of a rule. It can also be pure SQL
with placeholders.

You can define a macro with the `lnms` command. `config.php` also
works, but we do not recommend it. A macro is a boolean test, or it
returns a value for the rule. The value is an integer, a float, or a
string.

This example adds a macro that returns the delta of a sensor:

```bash
lnms config:set alert.macros.rule.sensor_delta 'ABS(%sensors.sensor_current - %sensors.sensor_prev)'
```

This example adds a boolean test macro through `config.php`:

```php
$config['alert']['macros']['rule']['is_debian'] = '%devices.features ~ "@debian@"';
```

You can then use these macros in the alerting rules. For example:

```bash
... macros.sensor_delta_current > 10 AND macros.rule.is_debian = 1 ...
```

## Writing Macros

The name of the macro sets its type. A name that ends in `_perc` or
`_delta` is an integer. You can then compare the value. Any other name
is a boolean test. Such a macro appears as a `yes` or `no` selection in
the rule.

A macro can hold placeholders. LibreNMS replaces each placeholder with
the real value at the evaluation of the rule. A placeholder starts with
`%` and holds the real value of a sensor, a port, a device, or another
entity. For example, LibreNMS replaces `%sensors.sensor_current` with
the real value of the sensor. The `%` prefix is optional, but we
recommend it. It prevents ambiguity.

The content of a macro is a valid SQL statement or a valid rule
expression.

## Pre-defined Macros

### Billing

#### Over quota (Boolean)

Entity: `macros.bill_quota_over_quota`

Description: it returns true when the bill is over its quota.

Source: `((bills.total_data \/ bills.bill_quota)*100) && bills.bill_type = "quota"`

#### Over usage (Boolean)

Entity: `macros.bill_cdr_over_quota`

Description: it returns true when the bill is over its usage.

Source: `((bills.rate_95th \/ bills.bill_cdr)*100) && bills.bill_type = "cdr"`

### Components

### Component (Boolean)

Entity: `macros.component`

Description: it selects only the components that are not deleted, ignored, or disabled.

Source: `(component.disabled = 0 && component.ignore = 0)`

### Component (Critical) (Boolean)

Entity: `macros.component_critical`

Description: it selects only the components in a critical state.

Source: `(component.status = 2 && macros.component)`

### Component (Up) (Boolean)

Entity: `macros.component_normal`

Description: it selects only the components in a normal state.

Source: `(component.status = 0 && macros.component)`


### Component (Warning) (Boolean)

Entity: `macros.component_warning`

Description: it selects only the components in a warning state.

Source: `(component.status = 1 && macros.component)`

### Device

#### Device (Boolean)

Entity: `macros.device`

Description: it selects only the devices that are not deleted, ignored, or disabled.

Source: `(devices.disabled = 0 AND devices.ignore = 0)`

#### Device CPU average percentage (Decimal)

Entity: `macros.device_cpu_avg_perc`

Description: Returns the average CPU usage percentage across all processors on the device. Returns `0` when no processor data is available.

Source: `COALESCE((SELECT AVG(p.processor_usage) FROM processors AS p WHERE p.device_id = %devices.device_id), 0)`

#### Device component down [JunOS]

Entity: `macros.device_component_down_junos`

Description: a device component such as a fan or a PSU is down, on a JunOS device.

source. `sensors.sensor_class = "state" && sensors.sensor_current != "6" && (sensors.sensor_type = "jnxFruState" || sensors.sensor_type = "jnxFruTable") && sensors.sensor_current != "2" && sensors.sensor_alert = "1"`


#### Device component down [Cisco]

Entity: `macros.device_component_down_cisco`

Description: a device component such as a fan or a PSU is down, on a Cisco device.

Example: `sensors.sensor_current != "1" && sensors.sensor_current != "5" && sensors.sensor_type REGEXP "^cisco.*State$" && sensors.sensor_alert = "1"`


#### Device is up (Boolean)

Entity: `macros.device_up`

Description: it selects only the devices that are up.

Implies: macros.device

Source: `(devices.status = 1 AND macros.device)`

#### Device is down (Boolean)

Entity: `macros.device_down`

Description: it selects only the devices that are down.

Implies: macros.device

Source: `(devices.status = 0 AND macros.device)`

### ICMP

#### ICMP Latency Variance (Decimal)

Entity: `macros.ping_rtt_variance_perc`

Description: the percentage difference between the last ICMP latency and the rolling average.

Source: `((device_stats.ping_rtt_last - device_stats.ping_rtt_avg) \/ device_stats.ping_rtt_avg) * 100`

#### ICMP Packet Loss Variance (Decimal)

Entity: `macros.ping_loss_variance_perc`

Description: the percentage difference between the last ICMP packet loss and the rolling average.

Source: `((device_stats.ping_loss_last - device_stats.ping_loss_avg) \/ device_stats.ping_loss_avg) * 100`

### Time

#### Now (Datetime)

Entity: `macros.now`

Description: an alias of the MySQL `NOW()` function.

Source: `NOW()`

#### Past N Minutes (Datetime)

Entity: `macros.past_$m`

Description: it returns a MySQL timestamp `$` minutes in the past.
`$` must be one of the supported resolutions.

Example: `macros.past_5m` is Last 5 Minutes.

Resolution: 5,10,15,30,60

Source: `DATE_SUB(NOW(),INTERVAL $ MINUTE)`

### Packet Loss

Entity: `(macros.packet_loss_5m)`

Description: the packet loss percentage of the device in the last 5 minutes. **BROKEN**: it returns only 100 for down, or 0.
 
Example: `macros.packet_loss_5m` > 50

Entity: `(macros.packet_loss_15m)`

Description: the packet loss percentage of the device in the last 15 minutes. **BROKEN**: it returns only 100 for down, or 0.

Example: `macros.packet_loss_15m` > 50

### Ports

### Port (Boolean)

Entity: `macros.port`

Description: it selects only the ports that are not deleted, ignored, or disabled.

Source: `(ports.deleted = 0 AND ports.ignore = 0 AND ports.disabled = 0)`

### Port out error percent (Decimal)

Entity: `macros.port_out_error_perc`

Description: it returns the outbound error percentage of the port.

Source: `((ports.ifOutErrors_rate / ports.ifOutUcastPkts_rate)*100)`

### Port in error percent (Decimal)

Entity: `macros.port_in_error_perc`

Description: it returns the inbound error percentage of the port.

Source: `((ports.ifInErrors_rate / ports.ifInUcastPkts_rate)*100)`


#### Port is up (Boolean)

Entity: `macros.port_up`

Description: it selects only the ports that are up and must be up.

Implies: macros.port

Source: `(ports.ifOperStatus = up AND ports.ifAdminStatus = up AND macros.port)`

#### Port is down (Boolean)

Entity: `macros.port_down`

Description: it selects only the ports that are down.

Implies: macros.port

Source: `(ports.ifOperStatus != "up" AND ports.ifAdminStatus != "down" AND macros.port)`

#### Port-Usage in Percent (Decimal)

Entity: `macros.port_usage_perc`

Description: it returns the port use in percent. The value is the higher one of the inbound use and the outbound use.

Source: `((SELECT IF(ports.ifOutOctets_rate>ports.ifInOctets_rate,
ports.ifOutOctets_rate, ports.ifInOctets_rate)*8) /
ports.ifSpeed)*100`


#### Ports in usage perc (Int)

Entity: `macros.port_in_usage_perc`

Description: 

Source: `((ports.ifInOctets_rate*8) \/ ports.ifSpeed)*100`

#### Ports out usage perc (Int)

Entity: `((ports.ifOutOctets_rate*8)/ports.ifSpeed)*100`

Description: 

Source: `((ports.ifOutOctets_rate*8) \/ ports.ifSpeed)*100`


#### Port now down (Boolean)

Entity: `macros.port_now_down`

Description: the ports that were up and are now down.

Source: `ports.ifOperStatus != ports.ifOperStatus_prev && ports.ifOperStatus_prev = "up" && ports.ifAdminStatus = "up" && macros.port`

#### Port has xDP neighbour (Boolean)

Entity: `macros.port_has_xdp_neighbours`

Description: the ports with an xDP neighbour, such as lldp or cdp.

Source: `(macros.port && links.local_port_id = ports.port_id)`

#### Port has xDP neighbour already known in LibreNMS (Boolean)

Entity: `macros.port_has_xdp_neighbours_device`

Description: the ports with an xDP neighbour that LibreNMS already knows.

Source: `(macros.port_has_xdp_neighbours && links.remote_port_id IS NOT NULL)`

### Sensors

#### Sensor (Boolean)

Entity: `macros.sensor`

Description: it selects only the sensors that are not ignored.

Source: `(sensors.sensor_alert = 1)`

#### Sensor Port Link (Boolean)

Entity: `macros.sensor_port_link`

Description: it selects only the sensors with a linked port, where the port is up and the device is up.

Source: `(sensors.entPhysicalIndex_measured = "port" AND sensors.entPhysicalIndex = ports.ifIndex AND macros.port_up AND macros.port_up)`

#### State Sensors critical (Boolean)

Entity: `macros.state_sensor_critical`

Description: it selects only the state sensors in a critical state.

Source: `(sensors.sensor_current = state_translations.state_value AND state_translations.state_generic_value = 2)`


#### State Sensors ok (Boolean)

Entity: `macros.state_sensor_ok`

Description: it selects only the state sensors in an ok state.

Source: `(sensors.sensor_current = state_translations.state_value AND state_translations.state_generic_value = 0)`

#### State Sensors unknown (Boolean)

Entity: `macros.state_sensor_unknown`

Description: it selects only the state sensors in an unknown state.

Source: `(sensors.sensor_current = state_translations.state_value AND state_translations.state_generic_value = 3)`

#### State Sensors warning (Boolean)

Entity: `macros.state_sensor_warning`

Description: it selects only the state sensors in a warning state.

Source: `(sensors.sensor_current = state_translations.state_value AND state_translations.state_generic_value = 1)`

### Misc

#### PDU over amperage [APC]

Entity: `macros.pdu_over_amperage_apc`

Description: an APC PDU is above its amperage limit.

Source: `sensors.sensor_class = "current" && sensors.sensor_descr = "Bank Total" && sensors.sensor_current > sensors.sensor_limit && devices.os = "apc"`

#### Service (Boolean)

Entity: `macros.service`

Description: it selects only the services that are not disabled or ignored.

Source: `(services.service_disabled = 0 && services.service_ignore = 0)`

### Custom Macros

These are examples of custom macros that you can add.

#### Sensor Delta Current (Decimal)

Entity: `macros.sensor_delta`

Description: it returns the delta of a sensor.

Source: `ABS(sensors.sensor_current - sensors.sensor_prev)`

### Sensor Change percent (Decimal)

Entity: `macros.sensor_change_perc`

Description: it returns the percentage change of a sensor.

Source: `ABS((CAST(sensors.sensor_current as double) - sensors.sensor_prev)/sensors.sensor_current * 100)`
