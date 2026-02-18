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
  The original Tigo Technology Center MIB (`TTC-NOTIFICATIONS-MIB`) is available
  under `mibs/ttc/` for backward compatibility with existing integrations.

### Configuration

| Setting | Default | Description |
| ------- | ------- | ----------- |
| Destination Host | — | Hostname or IP of the trap receiver |
| Destination Port | `162` | UDP/TCP port on the receiver |
| Transport | `UDP` | `UDP` or `TCP` |
| Community | `public` | SNMPv2c community string |
| Trap OID | `LIBRENMS-NOTIFICATIONS-MIB::lnmsDefaultAlertEvent` | Notification OID defined in the MIB |
| PDU Type | `TRAPv2` | `TRAPv2` (one-way) or `INFORM` (acknowledged) |
| MIB Directory | `/opt/librenms/mibs/librenms` | Directory containing the MIB file(s) |

**Example:**

| Config | Example |
| ------ | ------- |
| Destination Host | noc.example.com |
| Destination Port | 162 |
| Transport | UDP |
| Community | monitoring |
| Trap OID | LIBRENMS-NOTIFICATIONS-MIB::lnmsDefaultAlertEvent |
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
`LIBRENMS-NOTIFICATIONS-MIB::lnmsDefaultAlertEvent`.

```
lnmsDaTitle s "{{ $alert->title }}"
lnmsDaAlertID i {{ $alert->id }}
lnmsDaEventID i {{ $alert->uid }}
lnmsDaState i {{ $alert->state }}
lnmsDaSeverity s "{{ $alert->severity }}"
lnmsDaRuleID i {{ $alert->rule_id }}
lnmsDaRuleName s "{{ $alert->name }}"
lnmsDaProcedure s "{{ $alert->proc }}"
lnmsDaTimestamp s "{{ $alert->timestamp }}"
@if ($alert->state == 0)
lnmsDaTimeElapsed s "{{ $alert->elapsed }}"
@endif
lnmsDaDeviceID i {{ $alert->device_id }}
lnmsDaDevHostname s "{{ $alert->hostname }}"
lnmsDaDevSysName s "{{ $alert->sysName }}"
lnmsDaDevMgmtIP s "{{ $alert->ip }}"
lnmsDaDevOS s "{{ $alert->os }}"
lnmsDaDevType s "{{ $alert->type }}"
lnmsDaDevHardware s "{{ $alert->hardware }}"
lnmsDaDevVersion s "{{ $alert->version }}"
lnmsDaDevLocation s "{{ $alert->location }}"
lnmsDaDevUptime t {{ $alert->uptime }}
lnmsDaDevShortUptime s "{{ $alert->uptime_short }}"
lnmsDaACKNotes s "{{ $alert->alert_notes }}"
@if ($alert->faults)
@foreach ($alert->faults as $key => $value)
lnmsDaFaultDetail.{{ $key }} s "{{ $value['string'] }}"
@endforeach
@endif
```

#### Catch-All Template (TTC-NOTIFICATIONS-MIB — backward compatible)

For installations already using the TTC MIB, point the **MIB Directory** to
`/opt/librenms/mibs/ttc` and the **Trap OID** to
`TTC-NOTIFICATIONS-MIB::daEvent`.  Use the following template body:

```
daTitle s "{{ $alert->title }}"
daAlertID i {{ $alert->id }}
daEventID i {{ $alert->uid }}
daState i {{ $alert->state }}
daSeverity s "{{ $alert->severity }}"
daRuleID i {{ $alert->rule_id }}
daRuleName s "{{ $alert->name }}"
daProcedure s "{{ $alert->proc }}"
daTimestamp s "{{ $alert->timestamp }}"
@if ($alert->state == 0)
daTimeElapsed s "{{ $alert->elapsed }}"
@endif
daDeviceID i {{ $alert->device_id }}
daDevHostname s "{{ $alert->hostname }}"
daDevSysName s "{{ $alert->sysName }}"
daDevMgmtIP s "{{ $alert->ip }}"
daDevSysDescr s "{{ $alert->sysDescr }}"
daDevOS s "{{ $alert->os }}"
daDevType s "{{ $alert->type }}"
daDevHardware s "{{ $alert->hardware }}"
daDevVersion s "{{ $alert->version }}"
daDevSerial s "{{ $alert->serial }}"
daDevLocation s "{{ $alert->location }}"
daDevUptime t {{ $alert->uptime }}
daDevShortUptime s "{{ $alert->uptime_short }}"
daDevLongUptime s "{{ $alert->uptime_long }}"
daDevPurpose s "{{ $alert->description }}"
daDevNotes s "{{ $alert->notes }}"
daACKNotes s "{{ $alert->alert_notes }}"
daDevPingLoss s "{{ $alert->ping_loss }}"
daDevPingMin s "{{ $alert->ping_min }}"
daDevPingMax s "{{ $alert->ping_max }}"
daDevPingAvg s "{{ $alert->ping_avg }}"
@if ($alert->faults)
@foreach ($alert->faults as $key => $value)
daFaultTableEntryDetail.{{ $key }} s "{{ $value['string'] }}"
@endforeach
@endif
```

### MIB Installation

Copy the desired MIB directory to the LibreNMS host and configure the path:

```bash
# LibreNMS MIB (default)
cp -r /opt/librenms/mibs/librenms /opt/librenms/mibs/librenms

# TTC MIB (backward compatibility)
cp -r /opt/librenms/mibs/ttc /opt/librenms/mibs/ttc
```

To make the MIB globally available to Net-SNMP tools:

```bash
cp /opt/librenms/mibs/librenms/LIBRENMS-NOTIFICATIONS-MIB \
   /usr/share/snmp/mibs/
```
