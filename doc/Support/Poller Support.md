source: Support/Poller Support.md
path: blob/master/doc/

# poller.php

This document will explain how to use poller.php to debug issues or
manually running to process data.

## Command options

```bash
    LibreNMS 2014.master Poller

-h <device id> | <device hostname wildcard>  Poll single device
-h odd                                       Poll odd numbered devices  (same as -i 2 -n 0)
-h even                                      Poll even numbered devices (same as -i 2 -n 1)
-h all                                       Poll all devices

-i <instances> -n <number>                   Poll as instance <number> of <instances>
                                             Instances start at 0. 0-3 for -n 4

Debugging and testing options:
-r                                           Do not create or update RRDs
-f                                           Do not insert data into InfluxDB
-d                                           Enable debugging output
-v                                           Enable verbose debugging output
-m                                           Specify module(s) to be run. Comma separate modules, submodules may be added with /
```

`-h` Use this to specify a device via either id or hostname (including
wildcard using *). You can also specify odd and even. all will run
poller against all devices.

`-i` This can be used to stagger the poller process.

`-r` This option will suppress the creation or update of RRD files.

`-d` Enables debugging output (verbose output but with most sensitive
data masked) so that you can see what is happening during a poller
run. This includes things like rrd updates, SQL queries and response
from snmp.

`-v` Enables verbose debugging output with all data in tact.

`-m` This enables you to specify the module you want to run for poller.

# Poller Wrapper

We have a `poller-wrapper.py` script by [Job
Snijders](https://github.com/job). This script is currently the
default.

If you need to debug the output of poller-wrapper.py then you can add
`-d` to the end of the command - it is NOT recommended to do this in
cron.

# Poller config

These are the default poller config items. You can globally disable a
module by setting it to 0. If you just want to
disable it for one device then you can do this within the WebUI Device
-> Edit -> Modules.

```php
$config['poller_modules']['unix-agent']                  = false;
$config['poller_modules']['os']                          = true;
$config['poller_modules']['ipmi']                        = true;
$config['poller_modules']['sensors']                     = true;
$config['poller_modules']['processors']                  = true;
$config['poller_modules']['mempools']                    = true;
$config['poller_modules']['storage']                     = true;
$config['poller_modules']['netstats']                    = true;
$config['poller_modules']['hr-mib']                      = true;
$config['poller_modules']['ucd-mib']                     = true;
$config['poller_modules']['ipSystemStats']               = true;
$config['poller_modules']['ports']                       = true;
$config['poller_modules']['nac']                         = false;
$config['poller_modules']['bgp-peers']                   = true;
$config['poller_modules']['junose-atm-vp']               = false;
$config['poller_modules']['toner']                       = false;
$config['poller_modules']['ucd-diskio']                  = true;
$config['poller_modules']['wireless']                    = true;
$config['poller_modules']['ospf']                        = true;
$config['poller_modules']['cisco-ipsec-flow-monitor']    = false;
$config['poller_modules']['cisco-remote-access-monitor'] = false;
$config['poller_modules']['cisco-cef']                   = false;
$config['poller_modules']['cisco-sla']                   = false;
$config['poller_modules']['cisco-mac-accounting']        = false;
$config['poller_modules']['cipsec-tunnels']              = false;
$config['poller_modules']['cisco-ace-loadbalancer']      = false;
$config['poller_modules']['cisco-ace-serverfarms']       = false;
$config['poller_modules']['cisco-asa-firewall']          = false;
$config['poller_modules']['cisco-voice']                 = false;
$config['poller_modules']['cisco-cbqos']                 = false;
$config['poller_modules']['cisco-otv']                   = false;
$config['poller_modules']['cisco-vpdn']                  = false;
$config['poller_modules']['netscaler-vsvr']              = false;
$config['poller_modules']['aruba-controller']            = false;
$config['poller_modules']['entity-physical']             = true;
$config['poller_modules']['entity-state']                = false;
$config['poller_modules']['applications']                = true;
$config['poller_modules']['availability']                = true;
$config['poller_modules']['stp']                         = true;
$config['poller_modules']['ntp']                         = true;
$config['poller_modules']['services']                    = true;
$config['poller_modules']['loadbalancers']               = false;
$config['poller_modules']['mef']                         = false;
```

## OS based Poller config

You can enable or disable modules for a specific OS by add
corresponding line in `config.php` OS based settings have preference
over global. Device based settings have preference over all others

Poller performance improvement can be achieved by deactivating all
modules that are not supported by specific OS.

E.g. to deactivate spanning tree but activate unix-agent module for linux OS

```php
$config['os']['linux']['poller_modules']['stp']  = false;
$config['os']['linux']['poller_modules']['unix-agent'] = true;
```

## Poller modules

`unix-agent`: Enable the check_mk agent for external support for applications.

`system`: Provides information on some common items like uptime, sysDescr and sysContact.

`os`: Os detection. This module will pick up the OS of the device.

`ipmi`: Enables support for IPMI if login details have been provided for IPMI.

`sensors`: Sensor detection such as Temperature, Humidity, Voltages + More.

`processors`: Processor support for devices.

`mempools`: Memory detection support for devices.

`storage`: Storage detection for hard disks

`netstats`: Statistics for IP, TCP, UDP, ICMP and SNMP.

`hr-mib`: Host resource support.

`ucd-mib`: Support for CPU, Memory and Load.

`ipSystemStats`: IP statistics for device.

`ports`: This module will detect all ports on a device excluding ones
configured to be ignored by config options.

`nac`: Network Access Control (NAC) or 802.1X support.

`bgp-peers`: BGP detection and support.

`junose-atm-vp`: Juniper ATM support.

`toner`: Toner levels support.

`ucd-diskio`: Disk I/O support.

`wifi`: WiFi Support for those devices with support.

`ospf`: OSPF Support.

`cisco-ipsec-flow-monitor`: IPSec statistics support.

`cisco-remote-access-monitor`: Cisco remote access support.

`cisco-cef`: CEF detection and support.

`cisco-sla`: SLA detection and support.

`cisco-mac-accounting`: MAC Address account support.

`cipsec-tunnels`: IPSec tunnel support.

`cisco-ace-loadbalancer`: Cisco ACE Support.

`cisco-ace-serverfarms`: Cisco ACE Support.

`netscaler-vsvr`: Netscaler support.

`aruba-controller`: Aruba wireless controller support.

`entity-physical`: Module to pick up the devices hardware support.

`applications`: Device application support.

`availability`: Device Availability Calculation.

`cisco-asa-firewall`: Cisco ASA firewall support.

`mib`: Support for generic MIB parsing.

# Running

Here are some examples of running poller from within your install directory.

```bash
./poller.php -h localhost

./poller.php -h localhost -m ports
```

# Debugging

To provide debugging output you will need to run the poller process
with the `-d` flag. You can do this either against
all modules, single or multiple modules:

All Modules

```bash
./poller.php -h localhost -d
```

Single Module

```bash
./poller.php -h localhost -m ports -d
```

Multiple Modules

```bash
./poller.php -h localhost -m ports,entity-physical -d
```

Using `-d` shouldn't output much sensitive information, `-v` will so
it is then advisable to sanitise the output before pasting it
somewhere as the debug output will contain snmp details amongst other
items including port descriptions.

The output will contain:

DB Updates

RRD Updates

SNMP Response
