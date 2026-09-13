Wireless sensors on OpenWrt need an agent. Install the scripts from
`librenms-agent/snmp/Openwrt` on the device.

One net-snmp `pass_persist` handler (`openwrt-snmp-pass.sh`) serves the
wireless metrics. It exposes the OPENWRT-WIRELESS-MIB subtree. The handler
finds the radios and the VAPs at each request, so snmpd needs no per-radio
configuration.

A second handler (`lm-sensors-pass.sh`) serves the temperatures and the fan
speeds. It reads the thermal zones and the hwmon tachometer inputs, and it
emulates LM-SENSORS-MIB.

# Installation

1: Copy the scripts to `/usr/libexec/openwrt-snmp` on OpenWrt. The `wl*.sh`
helpers must stay next to `openwrt-snmp-pass.sh`, because it calls them with a
relative path:

```bash
mkdir -p /usr/libexec/openwrt-snmp
for s in openwrt-snmp-pass lm-sensors-pass wlInterfaces wlClients \
         wlFrequency wlNoiseFloor wlRate wlSNR; do
  wget -O "/usr/libexec/openwrt-snmp/$s.sh" \
    "https://raw.githubusercontent.com/librenms/librenms-agent/master/snmp/Openwrt/$s.sh"
done
chmod +x /usr/libexec/openwrt-snmp/*.sh
```

2: Register the two handlers in `/etc/config/snmpd`:

```
config pass
	option miboid '.1.3.6.1.4.1.66510.1.10'
	option prog '/usr/libexec/openwrt-snmp/openwrt-snmp-pass.sh'
	option persist '1'

config pass
	option miboid '.1.3.6.1.4.1.2021.13.16'
	option prog '/usr/libexec/openwrt-snmp/lm-sensors-pass.sh'
	option persist '1'
```

OS detection reads a `distro` extend and a `hardware` extend. Inline commands
produce these values, so no script is necessary:

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

3: Restart snmpd:

```bash
/etc/init.d/snmpd restart
```

# Validation and troubleshooting

To test the handler on OpenWrt, run this command:

```bash
/usr/libexec/openwrt-snmp/openwrt-snmp-pass.sh --snapshot
```

To walk the wireless subtree from the LibreNMS host, run this command:

```bash
snmpwalk -v2c -c your_community_string <openwrt-host> .1.3.6.1.4.1.66510.1.10
```

Then run the discovery for the wireless module again:

```bash
lnms device:discover <openwrt-host> -m wireless
```
