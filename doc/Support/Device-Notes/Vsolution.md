# V-SOL / V-Solution GPON OLT

LibreNMS supports V-SOL GPON OLTs (V1600GS and similar models) with per-ONU optical monitoring, transceiver inventory, state tracking, and SNMP trap handling.

## Supported Features

| Feature | Source | UI Location |
|---------|--------|-------------|
| Per-ONU optical sensors (temp, voltage, bias, TX/RX/OLT-RX power) | `gOnuOpticalInfoTable` | Ports > Transceivers |
| ONU transceiver metadata (vendor, model, serial, firmware, distance) | `gOnuDetailInfoTable`, `gOnuAuthInfoTable`, `gOnuRttTable` | Ports > Transceivers |
| OLT PON SFP sensors | `ponTransceiverTable` | Ports > Transceivers |
| ONU phase state (Working/LOS/Dying Gasp/Offline) | `gOnuStaInfoTable` | Health > State |
| PSU status | `sysPower1Status`, `sysPower2Status` | Health > State |
| GPON Class B+ optical power thresholds | Built-in | Alerts |
| VLAN names | Enterprise `vlanTable` | VLANs tab |
| Per-ONU traffic | Standard ifTable (GPON01ONU{n} interfaces) | Ports |
| SNMP traps (106 alarm types) | `dataAlarmTrap` | Alerts / Logs |
| Customers page integration | Optional port description parser | Customers |

## OLT Setup

### Enable SNMP

SNMP is disabled by default. Enable via SSH:

```bash
ssh admin@<olt-ip>
vtysh -c "configure terminal" \
  -c "snmp-server start" \
  -c "snmp-server community <community> ro" \
  -c "login-access-list permit snmp <monitoring-subnet> <mask>" \
  -c "end" -c "write memory"
```

### Enable SNMP Traps (optional)

To receive ONU alarms (dying-gasp, LOS, rogue ONU, optical power, etc.) via SNMP traps:

```bash
vtysh -c "configure terminal" \
  -c "snmp-server trap-host <librenms-ip> community <community>" \
  -c "end" -c "write memory"
```

LibreNMS handles the V-SOL `dataAlarmTrap` automatically. Enable trap reception in LibreNMS per the [SNMP Trap Handler docs](../Extensions/SNMP-Trap-Handler.md).

### Add to LibreNMS

```bash
lnms device:add <olt-hostname> -v2c -c <community>
```

## Customers Page (optional)

ONU ports can appear on the LibreNMS Customers page with subscriber serial numbers and equipment info. This requires switching to the V-SOL port description parser:

```bash
lnms config:set port_descr_parser includes/port-descr-parser-vsolution.inc.php
```

After the next poll cycle, ONU ports will show on the Customers page with:
- **Customer**: ONU serial number (GPON SN)
- **Circuit**: PON path (e.g., GPON0/1:2)
- **Notes**: ONU vendor and model

To revert to the default parser:
```bash
lnms config:set port_descr_parser includes/port-descr-parser.inc.php
```

## Known Limitations

- **Tagged VLAN port membership**: Q-BRIDGE-MIB only reports PVID/untagged membership. Tagged VLAN-to-port assignments are not exposed via SNMP by the OLT firmware.
- **Per-ONU bandwidth profiles**: Service profile rate limits are applied via CLI but not queryable per-ONU via SNMP. The Customers page `speed` field cannot be auto-populated.
- **Per-ONU BIP-8/FEC error counters**: Available via CLI (`show onu all-statistics`) but not exposed via SNMP.
- **10GE SFP+ port**: Firmware reports as "GE0/3" in ifTable. Uplink transceiver data will populate when an SFP+ module is inserted.

## Tested Hardware

| Model | Firmware | ONUs Tested |
|-------|----------|-------------|
| V1600GS | V1.2.0 / V4.0.0 | Huawei EG8145V5, Nokia G-140W-C, V-SOL V2804AX30T-H |

## MIB Architecture

The V1600GS uses two enterprise MIB trees under OID `.1.3.6.1.4.1.37950` (V-Solution):

| MIB | OID | Content |
|-----|-----|---------|
| V1600G | `.37950.1.1.6` | GPON-specific: per-ONU status, optical power, inventory, services |
| V1600GSwitch | `.37950.1.1.5` | Platform: ports, VLANs, system info, OLT SFP optics, alarms |
| V1600D | `.37950.1.1.5` | Legacy EPON MIB (kept for backward compatibility with V1600D EPON OLTs) |

Note: The CDATA MIBs (`FD-OLT-MIB`, `NSCRTV-FTTX-GPON-MIB` under enterprise `.17409`) are **not used** by V1600GS firmware despite CDATA hardware origins.
