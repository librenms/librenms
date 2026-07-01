To use wireless sensors on OpenWrt, install the OpenWrt scripts from
`librenms-agent/snmp/Openwrt` on the device. These scripts return per-radio and
aggregate wireless metrics via NET-SNMP extends.

# Installation

1. Copy the metric scripts to `/etc/librenms` on OpenWrt:

```bash
mkdir -p /etc/librenms
for s in distro wlInterfaces wlClients wlFrequency wlNoiseFloor wlRate wlSNR lm-sensors-pass; do
  wget -O "/etc/librenms/$s.sh" \
    "https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/Openwrt/$s.sh"
done
chmod +x /etc/librenms/*.sh
```

2. Add the extends to `/etc/config/snmpd`, one per metric (per radio). Example for a
   radio `wlan0`:

```
config extend
	option name 'interfaces'
	option prog '/etc/librenms/wlInterfaces.sh'

config extend
	option name 'clients-wlan0'
	option prog '/etc/librenms/wlClients.sh'
	option args 'wlan0'

config extend
	option name 'frequency-wlan0'
	option prog '/etc/librenms/wlFrequency.sh'
	option args 'wlan0'
```

   Repeat the per-radio blocks (`clients-*`, `frequency-*`, `noise-floor-*`, `rate-*`,
   `snr-*`) for each wireless interface. For setups with many radios, a sample script
   that generates these blocks from the live hostapd interfaces is provided in the agent
   README (`snmp/Openwrt/README.md`).

3. Restart snmpd:

```bash
/etc/init.d/snmpd restart
```

# Validation and troubleshooting

Validate script outputs directly on OpenWrt:

```bash
/etc/librenms/wlInterfaces.sh
/etc/librenms/wlClients.sh
/etc/librenms/wlClients.sh wlan0
```

Validate SNMP extend outputs from the LibreNMS host:

```bash
snmpwalk -v2c -c your_community_string <openwrt-host> NET-SNMP-EXTEND-MIB::nsExtendObjects
```

Then re-run discovery for the wireless module:

```bash
lnms device:discover <openwrt-host> -m wireless
```
