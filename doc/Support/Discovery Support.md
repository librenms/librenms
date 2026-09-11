# Discovery Support

This document explains how to use discovery. Use discovery to debug a
problem or to process data manually.

The basic command is:

`lnms device:discover HOSTNAME`

## Command options

```bash
Description:
  Discover information about existing devices, defines what will be polled

Usage:
  device:discover [options] [--] <device spec>

Arguments:
  device spec            Device spec to discover: device_id, hostname, wildcard (*), odd, even, all

Options:
  -m, --module=MODULE   Specify module(s) to be run. submodules may be added with /.  Multiple values allowed. (multiple values allowed)
  -h, --help            Display help for the given command. When no command is given display help for the list command
      --silent          Do not output any message
  -q, --quiet           Only errors are displayed. All other output is suppressed
  -V, --version         Display this application version
      --ansi|--no-ansi  Force (or disable --no-ansi) ANSI output
  -n, --no-interaction  Do not ask any interactive question
      --env[=ENV]       The environment the command should run under
  -v|vv|vvv, --verbose  Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

`<device spec>` selects a device by its id or its hostname. A hostname
can hold the wildcard `*`. You can also give `odd` or `even`. The value
`all` runs discovery against all devices. The value `new` polls only
the new devices and the devices that you selected for rediscovery.

`-v` enables debug output. This output shows the operation of a
discovery run.

`-vv` enables verbose debug output. This output holds SQL queries and
SNMP responses. LibreNMS masks the sensitive data where it can.

`-vvv` enables full debug output with all the data.

`-m` selects the module to run for discovery.

## Discovery wrapper

The `discovery-wrapper.py` script is based on `poller-wrapper.py` by
[Job Snijders](https://github.com/job). This script is the current
default.

To debug the output of `discovery-wrapper.py`, add `-d` to the end of
the command. Do NOT use this flag in cron.

You can also use `-m` with a comma separated list of modules. For more
information, read the [command options](#command-options) of
`lnms device:discover -h`.
An example is `/opt/librenms/discovery-wrapper.py 1 -m bgp-peers`.

To go back to `lnms device:discover`, replace this line:

`33  */6   * * *   librenms    /opt/librenms/discovery-wrapper.py 1 >> /dev/null 2>&1`

With this line. We do not recommend this change:

`33  */6   * * *   librenms    /opt/librenms/lnms device:discover all >> /dev/null 2>&1`

## Discovery config

These are the default discovery configuration items. To disable a
module globally, set it to 0. To disable a module for one device, use
the web interface at Device -> Settings -> Modules.

!!! setting "discovery/discovery_modules"
    ```bash
    lnms config:set discovery_modules.os true
    lnms config:set discovery_modules.ports true
    lnms config:set discovery_modules.ports-stack true
    lnms config:set discovery_modules.entity-physical true
    lnms config:set discovery_modules.entity-state false
    lnms config:set discovery_modules.processors true
    lnms config:set discovery_modules.mempools true
    lnms config:set discovery_modules.cisco-vrf-lite true
    lnms config:set discovery_modules.mac-accounting true
    lnms config:set discovery_modules.cisco-pw false
    lnms config:set discovery_modules.vrf false
    lnms config:set discovery_modules.cisco-cef false
    lnms config:set discovery_modules.slas false
    lnms config:set discovery_modules.cisco-otv false
    lnms config:set discovery_modules.ipv4-addresses true
    lnms config:set discovery_modules.ipv6-addresses true
    lnms config:set discovery_modules.route false
    lnms config:set discovery_modules.sensors true
    lnms config:set discovery_modules.storage true
    lnms config:set discovery_modules.hr-device true
    lnms config:set discovery_modules.discovery-protocols true
    lnms config:set discovery_modules.arp-table true
    lnms config:set discovery_modules.discovery-arp false
    lnms config:set discovery_modules.junose-atm-vp false
    lnms config:set discovery_modules.bgp-peers true
    lnms config:set discovery_modules.vlans true
    lnms config:set discovery_modules.vminfo false
    lnms config:set discovery_modules.printer-supplies false
    lnms config:set discovery_modules.ucd-diskio true
    lnms config:set discovery_modules.applications false
    lnms config:set discovery_modules.services true
    lnms config:set discovery_modules.stp true
    lnms config:set discovery_modules.ntp true
    lnms config:set discovery_modules.loadbalancers false
    lnms config:set discovery_modules.mef false
    lnms config:set discovery_modules.wireless true
    lnms config:set discovery_modules.fdb-table true
    lnms config:set discovery_modules.xdsl false
    ```

## OS based Discovery config

To enable or disable a module for one OS, use `lnms config:set`. An OS
based setting has preference over a global setting. A device based
setting has preference over all other settings.

Disable the modules that the OS does not support. This change improves
the discovery performance.

For example, to disable spanning tree and to enable the discovery-arp
module for the Linux OS, use these commands:

!!! setting "discovery/discovery_modules"
    ```bash
    lnms config:set os.linux.discovery_modules.stp false
    lnms config:set os.linux.discovery_modules.discovery-arp true
    ```

## Discovery modules

`os`: OS detection. This module finds the OS of the device.

`ports`: this module detects all ports on a device. It excludes the
ports that the configuration options ignore.

`ports-stack`: the same as `ports`, but for stacks.

`xdsl`: this module collects more metrics for xDSL interfaces.

`entity-physical`: this module finds the hardware support of the device.

`processors`: processor support for devices.

`mempools`: memory detection support for devices.

`cisco-vrf-lite`: VRF-Lite detection and support.

`ipv4-addresses`: IPv4 address detection.

`ipv6-addresses`: IPv6 address detection.

`route`: this module loads the routing table of the device, with
history data. The default route limit is 1000. To change the limit, use
`lnms config:set routes.max_number 1000`.

`sensors`: sensor detection for temperature, humidity, voltage, and more.

`storage`: storage detection for hard disks.

`hr-device`: processor and memory support through HOST-RESOURCES-MIB.

`discovery-protocols`: auto discovery module for xDP, OSPF, OSPFv3, and BGP.

`arp-table`: detection of the ARP table of the device.

`fdb-table`: detection of the forwarding database table of the device,
with history data.

`discovery-arp`: auto discovery through ARP.

`junose-atm-vp`: Juniper ATM support.

`bgp-peers`: BGP detection and support.

`vlans`: VLAN detection and support.

`mac-accounting`: MAC address account support.

`cisco-pw`: pseudowire detection and support.

`vrf`: VRF detection and support.

`cisco-cef`: CEF detection and support.

`slas`: SLA detection and support.

`vminfo`: detection of the VM guests for VMware ESXi, libvirt, and XCP-NG.

`printer-supplies`: toner level support.

`ucd-diskio`: disk I/O support.

`services`: support for *nix services.

## Running

These are examples of discovery in your install directory.

```bash
lnms device:discover localhost

lnms device:discover localhost -m ports
```

## Debugging

For debug output, run discovery with the `-v` flag. You can run it
against all modules, one module, or several modules:

All Modules

```bash
lnms device:discover localhost -vv
```

Single Module

```bash
lnms device:discover localhost -m ports -vv
```

Multiple Modules

```bash
lnms device:discover localhost -m ports,entity-physical -vv
```

The `-vv` flag gives little sensitive information. The `-vvv` flag
gives much more. Sanitise the output of `-vvv` before you send it to
another person. The debug output holds SNMP details, port descriptions,
and other data.

The output holds:

- DB Updates
- SNMP Response
