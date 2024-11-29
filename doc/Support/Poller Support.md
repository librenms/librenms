# lnms device:poll

This document will explain how to use `lnms device:poll` to debug issues or
manually running to process data.

## Command options

```bash
Description:
  Poll data from device(s) as defined by discovery

Usage:
  device:poll [options] [--] <device spec>

Arguments:
  device spec            Device spec to poll: device_id, hostname, wildcard (*), odd, even, all

Options:
  -m, --modules=MODULES  Specify single module to be run. Comma separate modules, submodules may be added with /
  -x, --no-data          Do not update datastores (RRD, InfluxDB, etc)
  -h, --help             Display help for the given command. When no command is given display help for the list command
  -q, --quiet            Do not output any message
  -V, --version          Display this application version
      --ansi|--no-ansi   Force (or disable --no-ansi) ANSI output
  -n, --no-interaction   Do not ask any interactive question
      --env[=ENV]        The environment the command should run under
  -v|vv|vvv, --verbose   Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## Poller Wrapper

We have a `poller-wrapper.py` script by [Job
Snijders](https://github.com/job). This script is currently the
default.

If you need to debug the output of poller-wrapper.py then you can add
`-d` to the end of the command - it is NOT recommended to do this in
cron.

## Poller config

These are the default poller config items. You can globally disable a
module by setting it to 0. If you just want to
disable it for one device then you can do this within the WebUI Device
-> Edit -> Modules.

!!! setting "poller/poller_modules"
    ```bash
    lnms config:set poller_modules.unix-agent false
    lnms config:set poller_modules.os true
    lnms config:set poller_modules.ipmi true
    lnms config:set poller_modules.sensors true
    lnms config:set poller_modules.processors true
    lnms config:set poller_modules.mempools true
    lnms config:set poller_modules.storage true
    lnms config:set poller_modules.netstats true
    lnms config:set poller_modules.hr-mib true
    lnms config:set poller_modules.ucd-mib true
    lnms config:set poller_modules.ipSystemStats true
    lnms config:set poller_modules.ports true
    lnms config:set poller_modules.nac false
    lnms config:set poller_modules.bgp-peers true
    lnms config:set poller_modules.junose-atm-vp false
    lnms config:set poller_modules.printer-supplies false
    lnms config:set poller_modules.ucd-diskio true
    lnms config:set poller_modules.wireless true
    lnms config:set poller_modules.ospf true
    lnms config:set poller_modules.cisco-ipsec-flow-monitor false
    lnms config:set poller_modules.cisco-remote-access-monitor false
    lnms config:set poller_modules.cisco-cef false
    lnms config:set poller_modules.slas false
    lnms config:set poller_modules.cisco-mac-accounting false
    lnms config:set poller_modules.cipsec-tunnels false
    lnms config:set poller_modules.cisco-ace-loadbalancer false
    lnms config:set poller_modules.cisco-ace-serverfarms false
    lnms config:set poller_modules.cisco-asa-firewall false
    lnms config:set poller_modules.cisco-voice false
    lnms config:set poller_modules.cisco-cbqos false
    lnms config:set poller_modules.cisco-otv false
    lnms config:set poller_modules.cisco-vpdn false
    lnms config:set poller_modules.netscaler-vsvr false
    lnms config:set poller_modules.aruba-controller false
    lnms config:set poller_modules.entity-physical true
    lnms config:set poller_modules.entity-state false
    lnms config:set poller_modules.applications true
    lnms config:set poller_modules.availability true
    lnms config:set poller_modules.stp true
    lnms config:set poller_modules.vminfo false
    lnms config:set poller_modules.ntp true
    lnms config:set poller_modules.services true
    lnms config:set poller_modules.loadbalancers false
    lnms config:set poller_modules.mef false
    lnms config:set poller_modules.mef false
    ```

## OS based Poller config

You can enable or disable modules for a specific OS by add
corresponding line in `config.php` OS based settings have preference
over global. Device based settings have preference over all others

Poller performance improvement can be achieved by deactivating all
modules that are not supported by specific OS.

E.g. to deactivate spanning tree but activate unix-agent module for linux OS

!!! setting "poller/poller_modules"
    ```bash
    lnms config:set os.linux.poller_modules.stp false
    lnms config:set os.linux.poller_modules.unix-agent true
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

`xdsl`: This module will collect more metrics for xdsl interfaces.

`nac`: Network Access Control (NAC) or 802.1X support.

`bgp-peers`: BGP detection and support.

`junose-atm-vp`: Juniper ATM support.

`printer-supplies`: Toner levels support.

`ucd-diskio`: Disk I/O support.

`wifi`: WiFi Support for those devices with support.

`ospf`: OSPF Support.

`cisco-ipsec-flow-monitor`: IPSec statistics support.

`cisco-remote-access-monitor`: Cisco remote access support.

`cisco-cef`: CEF detection and support.

`slas`: SLA detection and support.

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

## Running

Here are some examples of running poller from within your install directory.

```bash
lnms device:poll localhost

lnms device:poll localhost -m ports
```

## Debugging

To provide debugging output you will need to run the poller process
with the `-vv` flag. You can do this either against
all modules, single or multiple modules:

All Modules

```bash
lnms device:poll localhost -vv
```

Single Module

```bash
lnms device:poll localhost -m ports -vv
```

Multiple Modules

```bash
lnms device:poll localhost -m ports,entity-physical -vv
```

Using `-vv` shouldn't output much sensitive information, `-vvv` will so
it is then advisable to sanitise the output before pasting it
somewhere as the debug output will contain snmp details amongst other
items including port descriptions.

The output will contain:

DB Updates

RRD Updates

SNMP Response
