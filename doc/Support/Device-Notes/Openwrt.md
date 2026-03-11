To use wireless sensors on OpenWrt, install the OpenWrt scripts from
`librenms-agent/snmp/Openwrt` on the device. These scripts return per-radio and
aggregate wireless metrics via NET-SNMP extends.

# Installation

## Recommended setup (automatic generation)

1. Copy scripts to `/etc/librenms` on OpenWrt:

```bash
mkdir -p /etc/librenms
wget -O /etc/librenms/distro.sh https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/Openwrt/distro.sh
wget -O /etc/librenms/wlInterfaces.sh https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/Openwrt/wlInterfaces.sh
wget -O /etc/librenms/wlClients.sh https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/Openwrt/wlClients.sh
wget -O /etc/librenms/wlFrequency.sh https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/Openwrt/wlFrequency.sh
wget -O /etc/librenms/wlNoiseFloor.sh https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/Openwrt/wlNoiseFloor.sh
wget -O /etc/librenms/wlRate.sh https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/Openwrt/wlRate.sh
wget -O /etc/librenms/wlSNR.sh https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/Openwrt/wlSNR.sh
wget -O /etc/librenms/lm-sensors-pass.sh https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/Openwrt/lm-sensors-pass.sh
wget -O /etc/librenms/snmpd-config-generator.sh https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/Openwrt/snmpd-config-generator.sh
chmod +x /etc/librenms/*.sh
```

2. Generate OpenWrt extends and apply to `/etc/config/snmpd`:

```bash
/etc/librenms/snmpd-config-generator.sh
```

Copy the generated `config extend` / `config pass` sections into
`/etc/config/snmpd` and restart SNMP:

```bash
/etc/init.d/snmpd restart
```

The generator dynamically discovers hostapd interfaces (for example
`wlan01`, `wlan02`, `wlan12`, `wlan22`) and creates matching `clients-*`,
`frequency-*`, `noise-floor-*`, `rate-*`, and `snr-*` extends.

## Manual setup (legacy)

Manual `config extend` entries are still supported, but dynamic generation is
recommended to avoid stale interface names after WLAN/SSID changes.

# What gets discovered

- Client count (per interface + aggregate `clients-wlan`)
- Frequency (MHz)
- Noise floor (dBm)
- Rate TX/RX stats (`min`, `avg`, `max`)
- SNR stats (`min`, `avg`, `max`)
- Temperature sensors through `lm-sensors-pass.sh`

# Validation and troubleshooting

Validate script outputs directly on OpenWrt:

```bash
/etc/librenms/wlInterfaces.sh
/etc/librenms/wlClients.sh
/etc/librenms/wlClients.sh wlan02
```

Validate SNMP extend outputs from LibreNMS host:

```bash
snmpwalk -v2c -c <community> <openwrt-host> NET-SNMP-EXTEND-MIB::nsExtendCommand
snmpwalk -v2c -c <community> <openwrt-host> 'NET-SNMP-EXTEND-MIB::nsExtendOutput1Line."clients-wlan"'
```

If extends are missing or stale:

- Regenerate and reapply `/etc/config/snmpd` from `snmpd-config-generator.sh`
- Restart `snmpd`
- Re-run LibreNMS discovery for wireless module

```bash
lnms device:discover <openwrt-host> -m wireless
```

On the LibreNMS server, ensure SNMP MIB support is installed (`snmp-mibs-downloader`).
