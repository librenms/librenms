## SNMP Trap

SNMP traps are the standard way to push alert notifications to a northbound NMS or
event correlation system. This transport sends **SNMPv2c TRAPs or INFORMs** carrying
structured varbind data that is fully defined by the alert template — giving you
complete control over which OIDs and values are included.

The transport uses the system `snmptrap` binary (configurable under
**Settings → External → Binaries → snmptrap**).

### Requirements

- Net-SNMP tools installed on the LibreNMS host (`snmptrap` binary).
- A MIB file accessible on the LibreNMS host describing the trap structure.
  The LibreNMS-contributed MIB (`LIBRENMS-NOTIFICATIONS-MIB`) is shipped under
  `mibs/librenms/` and covers the default alert template.

### Configuration

| Setting | Default | Description |
| ------- | ------- | ----------- |
| Destination Host | — | Hostname or IP of the trap receiver |
| Destination Port | `162` | UDP/TCP port on the receiver |
| Transport | `UDP` | `UDP` or `TCP` |
| Community | `public` | SNMPv2c community string |
| Trap OID | `LIBRENMS-NOTIFICATIONS-MIB::defaultAlertEvent` | Notification OID defined in the MIB |
| PDU Type | `TRAPv2` | `TRAPv2` (one-way) or `INFORM` (acknowledged) |
| MIB Directory | `/opt/librenms/mibs/librenms` | Directory containing the MIB file(s) |

**Example:**

| Config | Example |
| ------ | ------- |
| Destination Host | noc.example.com |
| Destination Port | 162 |
| Transport | UDP |
| Community | monitoring |
| Trap OID | LIBRENMS-NOTIFICATIONS-MIB::defaultAlertEvent |
| PDU Type | TRAPv2 |
| MIB Directory | /opt/librenms/mibs/librenms |

### Alert Templates

The message body produced by the alert template is parsed as a sequence of
**varbind lines**, each with the form:

```
OID type value
```

where `type` is a Net-SNMP type character (`s` = string, `i` = integer,
`t` = timeticks, `o` = OID, …) and `value` may be a double-quoted string
containing spaces.  Lines beginning with `#` are treated as comments.

#### Catch-All Template (LIBRENMS-NOTIFICATIONS-MIB)

Create an alert template with the name **SNMP Trap — Default** and the
following body.  Assign it to transports that reference
`LIBRENMS-NOTIFICATIONS-MIB::defaultAlertEvent`.

```
defaultAlertTitle s "{{ $alert->title }}"
defaultAlertID i {{ $alert->id }}
defaultAlertEventID i {{ $alert->uid }}
defaultAlertState i {{ $alert->state }}
defaultAlertSeverity s "{{ $alert->severity }}"
defaultAlertRuleID i {{ $alert->rule_id }}
defaultAlertRuleName s "{{ $alert->name }}"
defaultAlertProcedure s "{{ $alert->proc }}"
defaultAlertTimestamp s "{{ $alert->timestamp }}"
@if ($alert->state == 0)
defaultAlertTimeElapsed s "{{ $alert->elapsed }}"
@endif
defaultAlertDeviceID i {{ $alert->device_id }}
defaultAlertDevHostname s "{{ $alert->hostname }}"
defaultAlertDevSysName s "{{ $alert->sysName }}"
defaultAlertDevMgmtIP s "{{ $alert->ip }}"
defaultAlertDevOS s "{{ $alert->os }}"
defaultAlertDevType s "{{ $alert->type }}"
defaultAlertDevHardware s "{{ $alert->hardware }}"
defaultAlertDevVersion s "{{ $alert->version }}"
defaultAlertDevLocation s "{{ $alert->location }}"
defaultAlertDevUptime t {{ $alert->uptime }}
defaultAlertDevShortUptime s "{{ $alert->uptime_short }}"
defaultAlertACKNotes s "{{ $alert->alert_notes }}"
@if ($alert->faults)
@foreach ($alert->faults as $key => $value)
defaultAlertFaultDetail.{{ $key }} s "{{ $value['string'] }}"
@endforeach
@endif
```

### MIB Installation

Copy the desired MIB directory to the LibreNMS host and configure the path:

```bash
# LibreNMS MIB (default)
cp -r /opt/librenms/mibs/librenms /opt/librenms/mibs/librenms
```

To make the MIB globally available to Net-SNMP tools:

```bash
cp /opt/librenms/mibs/librenms/LIBRENMS-NOTIFICATIONS-MIB \
   /usr/share/snmp/mibs/
```
