source: Alerting/Rules.md
path: blob/master/doc/

# Rules

Rules are defined using a logical language.

The GUI provides a simple way of creating rules.

Creating more complicated rules which may include maths calculations
and MySQL queries can be done using [macros](Macros.md)

#### Video on how the alert rules work in LibreNMS

[Alert Rules](https://youtu.be/ryv0j8GEkhM)

#### Video on how to use alert rule with wildcards

[Alert Rules wildcard](https://youtu.be/eYYioFNcrAk)

## Syntax

Rules must consist of at least 3 elements: An __Entity__, a __Condition__ and a __Value__.
Rules can contain braces and __Glues__.
__Entities__ are provided from Table and Field from the database. For Example: `ports.ifOperStatus`.

__Conditions__ can be any of:

- Equals `=`
- Not Equals `!=`
- In `IN`
- Not In `NOT IN`
- Begins with `LIKE ('...%')`
- Doesn't begin with `NOT LIKE ('...%')`
- Contains `LIKE ('%...%')`
- Doesn't Contain `NOT LIKE ('%...%')`
- Ends with `LIKE ('%...')`
- Doesn't end with `NOT LIKE ('%...')`
- Between `BETWEEN`
- Not Between `NOT BETWEEN`
- Is Empty `= ''`
- Is Not Empty `!= '''`
- Is Null `IS NULL`
- Is Not Null `IS NOT NULL`
- Greater `>`
- Greater or Equal `>=`
- Less `<`
- Less or Equal `<=`
- Regex `REGEXP`

__Values__ can be an entity or any data. If using macros as value you
must include the macro name into backticks. i.e. \`macros.past_60m\`

__Note__: Regex supports MySQL Regular expressions.

Arithmetics are allowed as well.

## Options

Here are some of the other options available when adding an alerting rule:

- Rule name: The name associated with the rule.
- Severity: How "important" the rule is.
- Max alerts: The maximum number of alerts sent for the event.  `-1` means unlimited.
- Delay: The amount of time in seconds to wait after a rule is matched
  before sending an alert out transport.
- Interval: The interval of time in seconds between alerts for an
  event until Max alert is reached.
- Mute alerts: Disables sending alert rule through alert
  transport. But will still show the alert in the Web UI.
- Invert match: Invert the matching rule (ie. alert on items that
  _don't match the rule).
- Recovery alerts: This will disable the recovery notification from
  being sent if turned off.

## Advanced

On the Advanced tab, you can specify some additional options for the alert rule:

- Override SQL: Enable this if you using a custom query
- Query: The query to be used for the alert.

- An example of this would be an average rule for all CPUs over 10%

```sql
SELECT *,AVG(processors.processor_usage) as cpu_avg FROM devices,processors WHERE (devices.device_id = ? AND devices.device_id = processors.device_id) AND (devices.status = 1 && (devices.disabled = 0 && devices.ignore = 0)) = 1 HAVING AVG(processors.processor_usage)  > 10
```

> The 10 would then contain the average CPU usage value, you can
> change this value to be whatever you like.

- You will to need copy and paste this into the Alert Rule under
  Advanced then paste into Query box and switch the Override SQL.

## Procedure

You can associate a rule to a procedure by giving the URL of the
procedure when creating the rule. Only links like "http://" are
supported, otherwise an error will be returned. Once configured,
procedure can be opened from the Alert widget through the "Open"
button, which can be shown/hidden from the widget configuration box.

## Examples

Alert when:

- Device goes down: `devices.status != 1`
- Any port changes: `ports.ifOperStatus != 'up'`
- Root-directory gets too full: `storage.storage_descr = '/' AND
  storage.storage_perc >= '75'`
- Any storage gets fuller than the 'warning': `storage.storage_perc >= storage_perc_warn`
- If device is a server and the used storage is above the warning
  level, but ignore /boot partitions: `storage.storage_perc >
  storage.storage_perc_warn AND devices.type = "server" AND
  storage.storage_descr != "/boot"`
- VMware LAG is not using "Source ip address hash" load balancing:
  `devices.os = "vmware" AND ports.ifType = "ieee8023adLag" AND
  ports.ifDescr REGEXP "Link Aggregation .*, load balancing algorithm:
  Source ip address hash"`
- Syslog, authentication failure during the last 5m:
  `syslog.timestamp >= macros.past_5m AND syslog.msg REGEXP ".*authentication failure.*"`
- High memory usage: `macros.device_up = 1 AND mempools.mempool_perc >=
 90 AND mempools.mempool_descr REGEXP "Virtual.*"`
- High CPU usage(per core usage, not overall): `macros.device_up
  = 1 AND processors.processor_usage >= 90`
- High port usage, where description is not client & ifType is not
  softwareLoopback: `macros.port_usage_perc >= 80 AND
  port.port_descr_type != "client" AND ports.ifType != "softwareLoopback"`
- Alert when mac address is located on your network `ipv4_mac.mac_address = "2c233a756912"`

## Alert Rules Collection

You can also select Alert Rule from the Alerts Collection. These Alert
Rules are submitted by users in the community :) If would like to
submit your alert rules to the collection, please submit them here [Alert Rules Collection](https://github.com/librenms/librenms/blob/master/misc/alert_rules.json)

![Alert Rules Collection](/img/alert-rules-collection.png)
