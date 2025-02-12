# Macros

Macros are shorthands to either portion of rules or pure SQL enhanced with placeholders.

Macros can be defined through the `lnms` command. Using the `config.php` is discouraged but supported. A macro can either be truthful (boolean test) or return a value (integer, float, string) that can be used in the rule.

Example for adding a a macro that returns the delta of a sensor:

```bash
lnms config:set alert.macros.rule.sensor_delta_current 'ABS(%sensors.sensor_current - %sensors.sensor_prev)'
```


Example for adding macro through `config.php` that is a boolean test:

```php
$config['alert']['macros']['rule']['is_debian'] = '%devices.features ~ "@debian@"';
```

These rules can then be used in the alerting rules. Example:

```
... macros.sensor_delta_current > 10 AND macros.rule.is_debian = 1 ...
```

## Writing Macros

The naming of the macro determines the type of the macro. If the macro name ends with `_perc` it interpreted as integer allowing for a comparison of the value. Any other name is a boolean test that will appear as `yes` or `no` selection in the rule.

The macro can contain placeholders that are replaced with the actual values when the rule is evaluated. The placeholders are prefixed with `%` that represents the actual value of the sensor, port, device, etc. For example `%sensors.sensor_current` will be replaced with the actual value of the sensor. While the prefix `%` is optional, it is recommended to use it to avoid ambiguity.

The contents of a macro can be any valid SQL statement or valid rule expression.

## Pre-defined Macros

### Billing

#### Over quota (Boolean)

Entity: `macros.bill_quota_over_quota`

Description: true or false if the bill is over quota.

Source: `((bills.total_data \/ bills.bill_quota)*100) && bills.bill_type = "quota"`

#### Over usage (Boolean)

Entity: `macros.bill_cdr_over_quota`

Description: true or false if the bill is over usage.

Source: `((bills.rate_95th \/ bills.bill_cdr)*100) && bills.bill_type = "cdr"`

### Components

### Component (Boolean)

Entity: `macros.component`

Description: Only select components that aren't deleted, ignored or disabled.

Source: `(component.disabled = 0 && component.ignore = 0)`

### Component (Critical) (Boolean)

Entity: `macros.component_critical`

Description: Only select components that are in a critical state.

Source: `(component.status = 2 && macros.component)`

### Component (Up) (Boolean)

Entity: `macros.component_normal`

Description: Only select components that are in a normal state.

Source: `(component.status = 0 && macros.component)`


### Component (Warning) (Boolean)

Entity: `macros.component_warning`

Description: Only select components that are in a warning state.

Source: `(component.status = 1 && macros.component)`

### Device

#### Device (Boolean)

Entity: `macros.device`

Description: Only select devices that aren't deleted, ignored or disabled.

Source: `(devices.disabled = 0 AND devices.ignore = 0)`

#### Device component down [JunOS]

Entity: `macros.device_component_down_junos`

Description: Device component is down such as Fan, PSU, etc for JunOS devices.

source. `sensors.sensor_class = "state" && sensors.sensor_current != "6" && (sensors.sensor_type = "jnxFruState" || sensors.sensor_type = "jnxFruTable") && sensors.sensor_current != "2" && sensors.sensor_alert = "1"`


#### Device component down [Cisco]

Entity: `macros.device_component_down_cisco`

Description: Device component is down such as Fan, PSU, etc for Cisco devices.

Example: `sensors.sensor_current != "1" && sensors.sensor_current != "5" && sensors.sensor_type REGEXP "^cisco.*State$" && sensors.sensor_alert = "1"`


#### Device is up (Boolean)

Entity: `macros.device_up`

Description: Only select devices that are up.

Implies: macros.device

Source: `(devices.status = 1 AND macros.device)`

#### Device is down (Boolean)

Entity: `macros.device_down`

Description: Only select devices that are down.

Implies: macros.device

Source: `(devices.status = 0 AND macros.device)`

### Time

#### Now (Datetime)

Entity: `macros.now`

Description: Alias of MySQL built-in `NOW()` function.

Source: `NOW()`

#### Past N Minutes (Datetime)

Entity: `macros.past_$m`

Description: Returns a MySQL Timestamp dated `$` Minutes in the
past. `$` can only be a supported Resolution.

Example: `macros.past_5m` is Last 5 Minutes.

Resolution: 5,10,15,30,60

Source: `DATE_SUB(NOW(),INTERVAL $ MINUTE)`

### Packet Loss

Entity: `(macros.packet_loss_5m)`

Description: Packet loss % value for the device within the last 5 minutes. **BROKEN**, only return 100 (down) or 0.
 
Example: `macros.packet_loss_5m` > 50

Entity: `(macros.packet_loss_15m)`

Description: Packet loss % value for the device within the last 15 minutes. **BROKEN**, only return 100 (down) or 0.

Example: `macros.packet_loss_15m` > 50

### Ports

### Port (Boolean)

Entity: `macros.port`

Description: Only select ports that aren't deleted, ignored or disabled.

Source: `(ports.deleted = 0 AND ports.ignore = 0 AND ports.disabled = 0)`

### Port out error percent (Decimal)

Entity: `macros.port_out_error_perc`

Description: Return port out error percent.

Source: `((ports.ifOutErrors_rate / ports.ifOutUcastPkts_rate)*100)`

### Port in error percent (Decimal)

Entity: `macros.port_in_error_perc`

Description: Return port in error percent.

Source: `((ports.ifInErrors_rate / ports.ifInUcastPkts_rate)*100)`


#### Port is up (Boolean)

Entity: `macros.port_up`

Description: Only select ports that are up and also should be up.

Implies: macros.port

Source: `(ports.ifOperStatus = up AND ports.ifAdminStatus = up AND macros.port)`

#### Port is down (Boolean)

Entity: `macros.port_down`

Description: Only select ports that are down.

Implies: macros.port

Source: `(ports.ifOperStatus != "up" AND ports.ifAdminStatus != "down" AND macros.port)`

#### Port-Usage in Percent (Decimal)

Entity: `macros.port_usage_perc`

Description: Return port-usage (max value of in and out) in percent.

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

Description: Ports that were previously up and have now gone down.

Source: `ports.ifOperStatus != ports.ifOperStatus_prev && ports.ifOperStatus_prev = "up" && ports.ifAdminStatus = "up" && macros.port`

#### Port has xDP neighbour (Boolean)

Entity: `macros.port_has_xdp_neighbours`

Description: Ports that have an xDP (lldp, cdp, etc) neighbour.

Source: `(macros.port && links.local_port_id = ports.port_id)`

#### Port has xDP neighbour already known in LibreNMS (Boolean)

Entity: `macros.port_has_xdp_neighbours_device`

Description: Ports that have an xDP (lldp, cdp, etc) neighbour that is already known in LibreNMS.

Source: `(macros.port_has_xdp_neighbours && links.remote_port_id IS NOT NULL)`

### Sensors

#### Sensor (Boolean)

Entity: `macros.sensor`

Description: Only select sensors that aren't ignored.

Source: `(sensors.sensor_alert = 1)`

#### Sensor Port Link (Boolean)

Entity: `macros.sensor_port_link`

Description: Only selects sensors that have a port linked to them, the port is up and the device is up.

Source: `(sensors.entPhysicalIndex_measured = "port" AND sensors.entPhysicalIndex = ports.ifIndex AND macros.port_up AND macros.port_up)`

#### State Sensors critical (Boolean)

Entity: `macros.state_sensor_critical`

Description: Only select state sensors that are in a critical state.

Source: `(sensors.sensor_current = state_translations.state_value AND state_translations.state_generic_value = 2)`


#### State Sensors ok (Boolean)

Entity: `macros.state_sensor_ok`

Description: Only select state sensors that are in a ok state.

Source: `(sensors.sensor_current = state_translations.state_value AND state_translations.state_generic_value = 0)`

#### State Sensors unknown (Boolean)

Entity: `macros.state_sensor_unknown`

Description: Only select state sensors that are in a unknown state.

Source: `(sensors.sensor_current = state_translations.state_value AND state_translations.state_generic_value = 3)`

#### State Sensors warning (Boolean)

Entity: `macros.state_sensor_warning`

Description: Only select state sensors that are in a warning state.

Source: `(sensors.sensor_current = state_translations.state_value AND state_translations.state_generic_value = 1)`

### Misc

#### PDU over amperage [APC]

Entity: `macros.pdu_over_amperage_apc`

Description: APC PDU over amperage

Source: `sensors.sensor_class = "current" && sensors.sensor_descr = "Bank Total" && sensors.sensor_current > sensors.sensor_limit && devices.os = "apc"`

### Custom Macros

Below are some examples of custom macros that can be be added.

#### Sensor Delta Current (Decimal)

Entity: `macros.sensor_delta_current`

Description: Returns the delta of a sensor.

Source: `ABS(sensors.sensor_current - sensors.sensor_prev)`

### Sensor Change percent (Decimal)

Entity: `macros.sensor_change_perc`

Description: Returns the percent change of a sensor.

Source: `ABS((CAST(sensors.sensor_current as double) - sensors.sensor_prev)/sensors.sensor_current * 100)`
