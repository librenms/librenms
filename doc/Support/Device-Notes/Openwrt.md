Wireless and temperature/fan sensors on OpenWrt need something on the device
to answer SNMP for them. LibreNMS does not care what that is, only that it
serves two subtrees:

- `OPENWRT-WIRELESS-MIB` under `.1.3.6.1.4.1.66510.1.10` for wireless
  clients, frequency, noise floor, tx/rx rate, SNR, channel utilisation and
  transmit power.
- `LM-SENSORS-MIB` under `.1.3.6.1.4.1.2021.13.16` for temperatures and fan
  speeds.

The reference implementation is
[`snmpd-openwrt-metrics`](https://github.com/openwrt/packages/pull/30547), a
net-snmp AgentX subagent. It is proposed to `openwrt/packages` and not merged
yet, so until it is you need to build it from that pull request with the
OpenWrt SDK. Any other implementation that serves the two subtrees works too.

A device is detected as OpenWrt when its snmpd reports OpenWrt's own
sysObjectID (`.1.3.6.1.4.1.66510.3.1`), or the net-snmp sysObjectID together
with a sysDescr that starts with `OpenWrt` or `Linux OpenWrt`, which is what a
stock OpenWrt snmpd reports. A host that merely has OpenWrt in its name, such
as `OpenWrt-builder`, does not match. Neither rule needs an extra query.

Detection no longer reads the `distro` extend. A device that was detected
through that extend and has a renamed host (for example `Linux ap2 ...`) is
detected as `linux` after upgrading, until it reports OpenWrt's sysObjectID
or its hostname is `OpenWrt`. The `distro` extend is still used to read the
release, see below.

The sysObjectID is a snmpd setting, not something the agent serves. Where
the default `snmpd.conf` of the `net-snmp` package does not set it yet, set
it in the `system` section of `/etc/config/snmpd` and restart snmpd:

```
uci set snmpd.@system[0].sysObjectID='.1.3.6.1.4.1.66510.3.1'
uci commit snmpd
/etc/init.d/snmpd restart
```

A change to the default configuration only reaches new installs, because
`/etc/config/snmpd` is kept on upgrade, so an existing device needs the
commands above once.

The OpenWrt release and device model come from `OPENWRT-MIB` scalars under
`.1.3.6.1.4.1.66510.1.1` when the device serves them. Otherwise LibreNMS
falls back to a `distro` and a `hardware` extend:

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

Bitrates and channel utilisation are served in tenths of their unit
(`OpenwrtTenths`, display hint `d-1`), so `722` is 72.2 Mbit/s and `896` is
89.6 %. LibreNMS divides them back, nothing needs to be configured.

Wireless sensors are keyed by the interface name (for example `phy1-ap0`),
not by ifIndex, because the kernel assigns a new ifIndex whenever a VAP is
recreated. Each sensor is described as `SSID (interface)`, so the same SSID
on several bands stays distinguishable. An 802.11be multi-link interface has
no single frequency, transmit power or channel utilisation, so those are
discovered per link (`ap-mld0-link1`, ...) from `openwrtWirelessLinkTable`.
nl80211 reports no noise floor per link either, so a multi-link interface has
no noise floor sensor.

To check what a device is exposing, walk the OpenWrt and LM-SENSORS
subtrees from the LibreNMS host:

```bash
snmpwalk -v2c -c your_community_string <openwrt-host> .1.3.6.1.4.1.66510.1
snmpwalk -v2c -c your_community_string <openwrt-host> .1.3.6.1.4.1.2021.13.16
```

Then run discovery again:

```bash
lnms device:discover <openwrt-host> -m sensors,wireless
```
