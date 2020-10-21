source: doc/Support/Device-Notes/Openwrt.md
path: blob/master/doc/

To use Wireless Sensors on Openwrt, an agent of sorts is required. The
purpose of the agent is to execute on the client (Openwrt) side, to ensure
that the needed Wireless Sensor information is returned for SNMP queries (from LibreNMS).

# Installation

## Openwrt

Two items are required on the Openwrt side - scripts to generate the necessary information (for
SNMP replies), and an SNMP extend configuration update (to return the information vs. the expected
query).

1: Install the scripts:

Copy the scripts from librenms-agent/snmp/Openwrt - preferably inside /etc/librenms on Openwrt (and add this
directory to /etc/sysupgrade.conf, to survive firmware updates).

The only file that needs to be edited is wlInterfaces.txt, which is a mapping from the wireless interfaces, to
the desired display name in LibreNMS. For example,
```
wlan0,wl-2.4G
wlan1,wl-5.0G
```

2: Update the Openwrt SNMP configuration, adding extend support for the Wireless Sensor queries:

`vi /etc/config/snmpd`, adding the following entries (assuming the scripts are installed in /etc/librenms, and are executable),
and update the network interfaces as needed to match the hardware,

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

NOTE, any of the scripts above can be tested simply by running the corresponding command.

NOTE, to check the output data from any of these extensions, on the LibreNMS machine, run (for example),

`snmpwalk -v 2c -c public -Osqnv <openwrt-host> 'NET-SNMP-EXTEND-MIB::nsExtendOutputFull."frequency-wlan0"'`

NOTE, on the LibreNMS machine, ensure that snmp-mibs-downloader is installed.

NOTE, on the AsuswrtMerlin machine, ensure that distro is installed (i.e. that the OS is correctly detected!).

3: Restart the snmp service on Openwrt:

`service snmpd restart`

And then wait for discovery and polling on LibreNMS!
