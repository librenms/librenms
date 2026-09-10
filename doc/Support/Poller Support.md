# Poller Support

This document explains how to use `lnms device:poll`. Use this command to
debug a problem or to process data manually.

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

[Job Snijders](https://github.com/job) wrote the `poller-wrapper.py`
script. This script is the current default.

To debug the output of `poller-wrapper.py`, add `-d` to the end of the
command. Do NOT use this flag in cron.

## Poller config

These are the default poller configuration items. To disable a module
globally, set it to `false`. To disable a module for one device, use
the web interface at Device -> Edit -> Modules.

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
    lnms config:set poller_modules.ospfv3 true
    lnms config:set poller_modules.cisco-ipsec-flow-monitor false
    lnms config:set poller_modules.cisco-remote-access-monitor false
    lnms config:set poller_modules.cisco-cef false
    lnms config:set poller_modules.slas false
    lnms config:set poller_modules.mac-accounting true
    lnms config:set poller_modules.cipsec-tunnels false
    lnms config:set poller_modules.cisco-ace-loadbalancer false
    lnms config:set poller_modules.cisco-ace-serverfarms false
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

To enable or disable a module for one OS, use
`lnms config:set os.<poller_module> false`. An OS based setting has
preference over a global setting. A device based setting has preference
over all other settings.

A disabled module that the OS does not support gives only a small
improvement in the poller performance.

For example, to disable spanning tree and to enable the unix-agent
module for the Linux OS, use these commands:

!!! setting "poller/poller_modules"
    ```bash
    lnms config:set os.linux.poller_modules.stp false
    lnms config:set os.linux.poller_modules.unix-agent true
    ```

## Poller modules

`unix-agent`: it enables the check_mk agent for external application support.

`system`: it gives information on common items such as the uptime, sysDescr, and sysContact.

`os`: OS detection. This module finds the OS of the device.

`ipmi`: it enables IPMI support when you supply the IPMI login details.

`sensors`: sensor detection for temperature, humidity, voltage, and more.

`processors`: processor support for devices.

`mempools`: memory detection support for devices.

`storage`: storage detection for hard disks.

`netstats`: statistics for IP, TCP, UDP, ICMP, and SNMP.

`hr-mib`: host resource support.

`ucd-mib`: support for CPU, memory, and load.

`ipSystemStats`: IP statistics for the device.

`ports`: this module detects all ports on a device. It excludes the
ports that the configuration options ignore.

`xdsl`: this module collects more metrics for xdsl interfaces.

`nac`: Network Access Control (NAC) support, also called 802.1X.

`bgp-peers`: BGP detection and support.

`junose-atm-vp`: Juniper ATM support.

`printer-supplies`: toner level support.

`ucd-diskio`: disk I/O support.

`wifi`: WiFi support for the devices with this capability.

`ospf`: OSPF support.

`ospfv3`: OSPFv3 support.

`cisco-ipsec-flow-monitor`: IPSec statistics support.

`cisco-remote-access-monitor`: Cisco remote access support.

`cisco-cef`: CEF detection and support.

`slas`: SLA detection and support.

`mac-accounting`: MAC address account support.

`cipsec-tunnels`: IPSec tunnel support.

`cisco-ace-loadbalancer`: Cisco ACE support.

`cisco-ace-serverfarms`: Cisco ACE support.

`netscaler-vsvr`: Netscaler support.

`aruba-controller`: Aruba wireless controller support.

`entity-physical`: this module finds the hardware support of the device.

`applications`: device application support.

`availability`: device availability calculation.

## Running

These are examples of the poller in your install directory.

```bash
lnms device:poll localhost

lnms device:poll localhost -m ports
```

## Debugging

For debug output, run the poller with the `-vv` flag. You can run it
against all modules, one module, or several modules:

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

The `-vv` flag gives little sensitive information. The `-vvv` flag
gives much more. Sanitise the output of `-vvv` before you send it to
another person. The debug output holds SNMP details, port descriptions,
and other data.

The output holds:

DB Updates

RRD Updates

SNMP Response
