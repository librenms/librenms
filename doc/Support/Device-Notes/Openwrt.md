To use wireless sensors on OpenWrt, install the OpenWrt scripts from
`librenms-agent/snmp/Openwrt` on the device. Wireless metrics are served by a
single net-snmp `pass_persist` handler (`openwrt-snmp-pass.sh`) that exposes the
OPENWRT-WIRELESS-MIB subtree; radios and VAPs are discovered live, so no
per-radio snmpd configuration is required. Temperatures ride a second
`pass_persist` handler using LM-SENSORS-MIB emulation.

# Installation

1. Copy the scripts to `/usr/libexec/openwrt-snmp` on OpenWrt (the `wl*.sh` helpers must sit
   next to `openwrt-snmp-pass.sh`, which calls them by relative path):

```bash
mkdir -p /usr/libexec/openwrt-snmp
for s in openwrt-snmp-pass lm-sensors-pass wlInterfaces wlClients \
         wlFrequency wlNoiseFloor wlRate wlSNR; do
  wget -O "/usr/libexec/openwrt-snmp/$s.sh" \
    "https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/Openwrt/$s.sh"
done
chmod +x /usr/libexec/openwrt-snmp/*.sh
```

2. Register the two handlers in `/etc/config/snmpd`:

```
config pass
	option miboid '.1.3.6.1.4.1.60652.102.1.10'
	option prog '/usr/libexec/openwrt-snmp/openwrt-snmp-pass.sh'
	option persist '1'

config pass
	option miboid '.1.3.6.1.4.1.2021.13.16.2.1'
	option prog '/usr/libexec/openwrt-snmp/lm-sensors-pass.sh'
	option persist '1'
```

   OS detection reads a `distro` and a `hardware` extend, produced by inline
   commands rather than scripts:

```
config extend
	option name 'distro'
	option prog '/bin/sh'
	option args '-c '\''. /etc/os-release; echo $PRETTY_NAME'\'''

config extend
	option name 'hardware'
	option prog '/bin/cat'
	option args '/tmp/sysinfo/model'
```

3. Restart snmpd:

```bash
/etc/init.d/snmpd restart
```

# Validation and troubleshooting

Exercise the handler directly on OpenWrt:

```bash
/usr/libexec/openwrt-snmp/openwrt-snmp-pass.sh --snapshot
```

Walk the wireless subtree from the LibreNMS host:

```bash
snmpwalk -v2c -c your_community_string <openwrt-host> .1.3.6.1.4.1.60652.102.1.10
```

Then re-run discovery for the wireless module:

```bash
lnms device:discover <openwrt-host> -m wireless
```
