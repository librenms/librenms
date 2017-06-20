source: Alerting/Rules.md

# <a name="rules">Rules</a>

Rules are defined using a logical language.
The GUI provides a simple way of creating basic rules.
Creating more complicated rules which may include maths calculations and MySQL queries can be 
done using [macros](Macros.md)

## <a name="rules-syntax">Syntax</a>

Rules must consist of at least 3 elements: An __Entity__, a __Condition__ and a __Value__.
Rules can contain braces and __Glues__.
__Entities__ are provided from Table and Field from the database. For Example: `%ports.ifOperStatus`.
> Please note that % is not required when adding alert rules via the WebUI. 

__Conditions__ can be any of:

- Equals `=`
- Not Equals `!=`
- Like `~`
- Not Like `!~`
- Greater `>`
- Greater or Equal `>=`
- Smaller `<`
- Smaller or Equal `<=`

__Values__ can be an entity or any single-quoted data.
__Glues__ can be either `&&` for `AND` or `||` for `OR`.

__Note__: The difference between `Equals` and `Like` (and its negation) is that `Equals` does a strict comparison and `Like` allows the usage of MySQL RegExp.

Arithmetics are allowed as well.

# <a name="extra">Options</a>

Here are some of the other options available when adding an alerting rule:

- Rule name: The name associated with the rule.
- Severity: How "important" the rule is.
- Max alerts: The maximum number of alerts sent for the event.  `-1` means unlimited.
- Delay: The amount of time in seconds to wait after a rule is matched before sending an alert.
- Interval: The interval of time in seconds between alerts for an event until Max is reached.
- Mute alerts: Disable sending alerts for this rule.
- Invert match: Invert the matching rule (ie. alert on items that _don't_ match the rule).

## <a name="rules-examples">Examples</a>

Alert when:

- Device goes down: `%devices.status != '1'`
- Any port changes: `%ports.ifOperStatus != 'up'`
- Root-directory gets too full: `%storage.storage_descr = '/' && %storage.storage_perc >= '75'`
- Any storage gets fuller than the 'warning': `%storage.storage_perc >= %storage_perc_warn`
- If device is a server and the used storage is above the warning level, but ignore /boot partitions: `%storage.storage_perc > %storage.storage_perc_warn && %devices.type = "server" && %storage.storage_descr !~ "/boot"`
- VMware LAG is not using "Source ip address hash" load balancing: `%devices.os = "vmware" && %ports.ifType = "ieee8023adLag" && %ports.ifDescr !~ "Link Aggregation @, load balancing algorithm: Source ip address hash"`
- Syslog, authentication failure during the last 5m: `%syslog.timestamp >= %macros.past_5m && %syslog.msg ~ "@authentication failure@"`
- High memory usage: `%macros.device_up = "1" && %mempools.mempool_perc >= "90" && %mempools.mempool_descr = "Virtual@"`
- High CPU usage(per core usage, not overall): `%macros.device_up = "1" && %processors.processor_usage >= "90"`
- High port usage, where description is not client & ifType is not softwareLoopback: `%macros.port_usage_perc >= "80" && %port.port_descr_type != "client" && %ports.ifType != "softwareLoopback"`

## <a name="rules-procedure">Procedure</a>
You can associate a rule to a procedure by giving the URL of the procedure when creating the rule. Only links like "http://" are supported, otherwise an error will be returned. Once configured, procedure can be opened from the Alert widget through the "Open" button, which can be shown/hidden from the widget configuration box.
