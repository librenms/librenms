Wireless sensors on AsuswrtMerlin need an agent. The agent runs on the
AsuswrtMerlin side. It returns the wireless sensor information for the
SNMP queries from LibreNMS.

# Installation

## AsuswrtMerlin

The AsuswrtMerlin side needs two items. The first item is a set of
scripts that generate the information for the SNMP replies. The second
item is an update to the SNMP extend configuration. This update returns
the information for each query.

1: Install the scripts:

Copy the scripts from `librenms-agent/snmp/Openwrt` into
`/etc/librenms` on AsuswrtMerlin. Then add this directory to
`/etc/sysupgrade.conf`, so that it survives a firmware update.

Only one file needs an edit. The file `wlInterfaces.txt` maps each
wireless interface to its display name in LibreNMS. For example:
```
wlan0,wl-2.4G
wlan1,wl-5.0G
```

2: Update the AsuswrtMerlin SNMP configuration. Add extend support for the wireless sensor queries:

Run `vi /etc/config/snmpd` and add the entries below. These entries
assume executable scripts in `/etc/librenms`. Change the network
interfaces to match your hardware.

```
config extend
        option name     interfaces
        option prog     "/bin/cat /etc/librenms/wlInterfaces.txt"
config extend
        option name     clients-wlan0
        option prog     "/etc/librenms/wlClients.sh wlan0"
config extend
        option name     clients-wlan1
        option prog     "/etc/librenms/wlClients.sh wlan1"
config extend
        option name     clients-wlan
        option prog     "/etc/librenms/wlClients.sh"
config extend
        option name     frequency-wlan0
        option prog     "/etc/librenms/wlFrequency.sh wlan0"
config extend
        option name     frequency-wlan1
        option prog     "/etc/librenms/wlFrequency.sh wlan1"
config extend
        option name     rate-tx-wlan0-min
        option prog     "/etc/librenms/wlRate.sh wlan0 tx min"
config extend
        option name     rate-tx-wlan0-avg
        option prog     "/etc/librenms/wlRate.sh wlan0 tx avg"
config extend
        option name     rate-tx-wlan0-max
        option prog     "/etc/librenms/wlRate.sh wlan0 tx max"
config extend
        option name     rate-tx-wlan1-min
        option prog     "/etc/librenms/wlRate.sh wlan1 tx min"
config extend
        option name     rate-tx-wlan1-avg
        option prog     "/etc/librenms/wlRate.sh wlan1 tx avg"
config extend
        option name     rate-tx-wlan1-max
        option prog     "/etc/librenms/wlRate.sh wlan1 tx max"
config extend
        option name     rate-rx-wlan0-min
        option prog     "/etc/librenms/wlRate.sh wlan0 rx min"
config extend
        option name     rate-rx-wlan0-avg
        option prog     "/etc/librenms/wlRate.sh wlan0 rx avg"
config extend
        option name     rate-rx-wlan0-max
        option prog     "/etc/librenms/wlRate.sh wlan0 rx max"
config extend
        option name     rate-rx-wlan1-min
        option prog     "/etc/librenms/wlRate.sh wlan1 rx min"
config extend
        option name     rate-rx-wlan1-avg
        option prog     "/etc/librenms/wlRate.sh wlan1 rx avg"
config extend
        option name     rate-rx-wlan1-max
        option prog     "/etc/librenms/wlRate.sh wlan1 rx max"
config extend
        option name     noise-floor-wlan0
        option prog     "/etc/librenms/wlNoiseFloor.sh wlan0"
config extend
        option name     noise-floor-wlan1
        option prog     "/etc/librenms/wlNoiseFloor.sh wlan1"
config extend
        option name     snr-wlan0-min
        option prog     "/etc/librenms/wlSNR.sh wlan0 min"
config extend
        option name     snr-wlan0-avg
        option prog     "/etc/librenms/wlSNR.sh wlan0 avg"
config extend
        option name     snr-wlan0-max
        option prog     "/etc/librenms/wlSNR.sh wlan0 max"
config extend
        option name     snr-wlan1-min
        option prog     "/etc/librenms/wlSNR.sh wlan1 min"
config extend
        option name     snr-wlan1-avg
        option prog     "/etc/librenms/wlSNR.sh wlan1 avg"
config extend
        option name     snr-wlan1-max
        option prog     "/etc/librenms/wlSNR.sh wlan1 max"
```

NOTE: to test a script above, run its command.

NOTE: to test the output of an extension, run this command on the
LibreNMS machine:

`snmpwalk -v 2c -c public -Osqnv <openwrt-host> 'NET-SNMP-EXTEND-MIB::nsExtendOutputFull."frequency-wlan0"'`

NOTE: the LibreNMS machine needs `snmp-mibs-downloader`.

NOTE: the AsuswrtMerlin machine needs `distro`. LibreNMS then detects
the OS correctly.

3: Restart the snmp service on AsuswrtMerlin:

`service snmpd restart`

Then wait for the discovery and the polling in LibreNMS.
