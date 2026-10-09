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
net-snmp AgentX subagent maintained in `openwrt/packages`.

The OpenWrt release and device model come from `OPENWRT-MIB` scalars under
`.1.3.6.1.4.1.66510.1.1` when the device serves them, and a device whose
snmpd reports OpenWrt's own sysObjectID (`.1.3.6.1.4.1.66510.3.1`) is
recognised without any extra query. Otherwise LibreNMS falls back to a
`distro` and a `hardware` extend:

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
