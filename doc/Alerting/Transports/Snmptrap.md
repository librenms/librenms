## SNMP Trap

An SNMP trap is the standard method to send an alert notification to a
northbound NMS or to an event correlation system. This transport sends
**SNMPv2c TRAPs or INFORMs**. Each message carries structured varbind
data from the alert template. You therefore control the OIDs and the
values.

The transport uses the system `snmptrap` binary. You can configure its
path under **Settings → External → Binaries → snmptrap**.

### Requirements

- Net-SNMP tools installed on the LibreNMS host (`snmptrap` binary).
- A MIB file on the LibreNMS host with the trap structure. The LibreNMS
  MIB (`LIBRENMS-NOTIFICATIONS-MIB`) is in `mibs/librenms/`. It covers
  the default alert template.

### Configuration

| Setting | Default | Description |
| ------- | ------- | ----------- |
| Destination Host | — | The hostname or IP address of the trap receiver |
| Destination Port | `162` | The UDP or TCP port on the receiver |
| Transport | `UDP` | `UDP` or `TCP` |
| Community | `public` | The SNMPv2c community string |
| Trap OID | `LIBRENMS-NOTIFICATIONS-MIB::defaultAlertEvent` | The notification OID from the MIB |
| PDU Type | `TRAPv2` | `TRAPv2` is one-way. `INFORM` is acknowledged |
| MIB Directory | `/opt/librenms/mibs/librenms` | The directory with the MIB files |

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

The transport reads the message body of the alert template as a
sequence of **varbind lines**. Each line has this form:

```
OID type value
```

`type` is a Net-SNMP type character. `s` is a string, `i` is an
integer, `t` is timeticks, and `o` is an OID. `value` can be a string
in double quotation marks with spaces. A line that starts with `#` is a
comment.

#### Catch-All Template (LIBRENMS-NOTIFICATIONS-MIB)

Create an alert template with the name **SNMP Trap — Default** and the
body below. Assign it to each transport with
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

Copy the MIB directory to the LibreNMS host. Then configure the path:

```bash
# LibreNMS MIB (default)
cp -r /opt/librenms/mibs/librenms /opt/librenms/mibs/librenms
```

To make the MIB available to all Net-SNMP tools:

```bash
cp /opt/librenms/mibs/librenms/LIBRENMS-NOTIFICATIONS-MIB \
   /usr/share/snmp/mibs/
```
